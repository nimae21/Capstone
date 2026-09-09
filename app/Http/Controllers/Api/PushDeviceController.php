<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobilePushDevice;
use App\Models\MobilePushDelivery;
use App\Services\FirebasePushSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class PushDeviceController extends Controller
{
    private function tokenId(Request $request): int
    {
        $token = $request->user()->currentAccessToken();
        abort_unless($token instanceof PersonalAccessToken && $token->exists, 403, 'A mobile login session is required.');
        abort_unless($request->user()->is_active, 403, 'This account is inactive.');
        return $token->id;
    }

    public function status(Request $request, FirebasePushSender $sender)
    {
        $tokenId = $this->tokenId($request);
        $input = $request->validate(['installation_id' => ['nullable', 'uuid']]);
        $device = isset($input['installation_id'])
            ? MobilePushDevice::eligible()->where('personal_access_token_id', $tokenId)->where('installation_id', $input['installation_id'])->first()
            : null;
        $lastSeen = Cache::get('mobile-push-worker-last-seen');
        return response()->json([
            'configured' => $sender->configured(),
            'worker_running' => $lastSeen && $lastSeen >= now()->subMinute()->timestamp,
            'registered' => (bool) $device,
        ]);
    }

    public function store(Request $request, FirebasePushSender $sender)
    {
        $tokenId = $this->tokenId($request);
        abort_unless($sender->configured(), 503, 'Push notifications are not configured on the server yet.');
        $input = $request->validate(['installation_id' => ['required', 'uuid'], 'token' => ['required', 'string', 'max:4096', 'min:20']]);
        DB::transaction(function () use ($input, $request, $tokenId) {
            // A reinstall/rotation replaces the old registration; one FCM token has one owner.
            $hash = hash('sha256', $input['token']);
            MobilePushDevice::where('token_hash', $hash)->where('installation_id', '!=', $input['installation_id'])->delete();
            $device = MobilePushDevice::where('installation_id', $input['installation_id'])->lockForUpdate()->first();
            // Discard pending alerts when the phone changes account/session.
            if ($device && $device->personal_access_token_id !== $tokenId) {
                MobilePushDelivery::where('device_id', $device->id)->delete();
            }
            MobilePushDevice::updateOrCreate(['installation_id' => $input['installation_id']], [
                'user_id' => $request->user()->id, 'personal_access_token_id' => $tokenId,
                'token' => $input['token'], 'token_hash' => $hash, 'enabled' => true, 'last_seen_at' => now(),
            ]);
        });
        return response()->json(['registered' => true]);
    }

    public function destroy(Request $request)
    {
        $tokenId = $this->tokenId($request);
        $input = $request->validate(['installation_id' => ['required', 'uuid']]);
        MobilePushDevice::where('personal_access_token_id', $tokenId)->where('installation_id', $input['installation_id'])->delete();
        return response()->json(['registered' => false]);
    }

    public function test(Request $request, FirebasePushSender $sender)
    {
        $tokenId = $this->tokenId($request);
        abort_unless($sender->configured(), 503, 'Push notifications are not configured on the server yet.');
        $input = $request->validate(['installation_id' => ['required', 'uuid']]);
        $device = MobilePushDevice::eligible()->where('personal_access_token_id', $tokenId)
            ->where('installation_id', $input['installation_id'])->firstOrFail();
        MobilePushDelivery::create([
            'device_id' => $device->id, 'personal_access_token_id' => $tokenId,
            'event_key' => 'test:'.Str::uuid(), 'kind' => 'test', 'available_at' => now(),
        ]);
        return response()->json(['message' => 'Test queued. Wait for the notification on this phone.'], 202);
    }
}
