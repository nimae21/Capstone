<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    public function test_settings_is_admin_only(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $user = new User(['first_name' => 'Customer', 'role' => 'user']);
        $user->id = 1;
        $this->actingAs($user)->get('/admin/settings')->assertForbidden();
        $user->role = 'admin';
        $this->actingAs($user)->get('/admin/settings')->assertOk()
            ->assertSee('Dark mode')->assertSee('admin-dark-mode')->assertSee('achilles.admin.theme.1');
    }

    public function test_log_view_shows_name_and_email_and_handles_system_events(): void
    {
        $user = new User(['first_name' => 'Alex', 'last_name' => 'Reyes', 'email' => 'alex@gmail.com']);
        $log = new ActivityLog(['action' => 'auth.login']);
        $log->created_at = now();
        $log->setRelation('user', $user);
        $log->setRelation('subject', null);
        $system = new ActivityLog(['action' => 'auth.failed']);
        $system->created_at = now();
        $system->setRelation('user', null);
        $system->setRelation('subject', null);
        $html = view('admin.logs.index', [
            'logs' => new LengthAwarePaginator(collect([$log, $system]), 2, 25),
            'categories' => collect(), 'activeCategory' => 'all', 'search' => '', 'from' => null, 'to' => null,
        ])->render();
        $this->assertStringContainsString('Alex Reyes', $html);
        $this->assertStringContainsString('alex@gmail.com', $html);
        $this->assertStringContainsString('System', $html);
        $this->assertLessThan(strpos($html, 'alex@gmail.com'), strpos($html, 'Alex Reyes'));
    }
}
