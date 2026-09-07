@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('page-subtitle', 'A full audit trail of admin and customer account activity.')

@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    * { font-family: 'Inter', sans-serif; }
    body { background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%); }

    .gradient-title {
        font-weight: 900 !important;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .panel {
        background: white;
        border-radius: 1.25rem;
        border: 1px solid #eef2f6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .category-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .category-tab:hover {
        border-color: #dc2626;
        color: #dc2626;
    }
    .category-tab.active {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-color: #dc2626;
        color: white;
    }
    .category-tab .count {
        font-size: 0.72rem;
        opacity: 0.8;
    }

    .filter-input {
        border: 1px solid #e2e8f0;
        border-radius: 0.6rem;
        padding: 0.5rem 0.85rem;
        font-size: 0.85rem;
    }
    .filter-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
    }

    .log-row td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
        font-size: 0.85rem;
    }

    .event-badge {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
    }
    .event-badge.created { background: #dcfce7; color: #16a34a; }
    .event-badge.updated { background: #fef3c7; color: #b45309; }
    .event-badge.deleted { background: #fee2e2; color: #dc2626; }
    .event-badge.login,
    .event-badge.registered { background: #dbeafe; color: #2563eb; }
    .event-badge.logout { background: #f1f5f9; color: #64748b; }
    .event-badge.failed { background: #fee2e2; color: #dc2626; }

    .diff-toggle {
        font-size: 0.75rem;
        color: #dc2626;
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
    .diff-box {
        display: none;
        margin-top: 0.5rem;
        background: #fafcfd;
        border: 1px solid #eef2f6;
        border-radius: 0.5rem;
        padding: 0.6rem 0.75rem;
        font-size: 0.75rem;
    }
    .diff-box.open { display: block; }
    .diff-field { margin-bottom: 0.3rem; }
    .diff-field .field-name { font-weight: 700; color: #1e293b; }
    .diff-old { color: #dc2626; text-decoration: line-through; }
    .diff-new { color: #16a34a; }
</style>
@endsection

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-5">
        <h1 class="gradient-title text-3xl">Activity Logs</h1>
        <p class="text-gray-500 text-sm mt-1">Every admin action and account event, grouped by category.</p>
    </div>

    <!-- Category Tabs -->
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ request()->fullUrlWithQuery(['category' => 'all']) }}"
           class="category-tab {{ $activeCategory === 'all' ? 'active' : '' }}">
            All
        </a>
        @foreach($categories as $cat)
            <a href="{{ request()->fullUrlWithQuery(['category' => $cat->category_name]) }}"
               class="category-tab {{ $activeCategory === $cat->category_name ? 'active' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $cat->category_name)) }}
                <span class="count">({{ $cat->total }})</span>
            </a>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="panel p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="category" value="{{ $activeCategory }}">

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Action or user name/email..."
                       class="filter-input w-full">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="filter-input">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="filter-input">
            </div>

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-4 py-2 rounded-lg">
                Filter
            </button>

            @if($search || $from || $to)
                <a href="{{ request()->fullUrlWithQuery(['category' => $activeCategory, 'search' => null, 'from' => null, 'to' => null]) }}"
                   class="text-sm text-gray-400 hover:text-red-600 font-semibold px-2">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Log Table -->
    <div class="panel overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Event</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="log-row">
                        <td class="text-gray-500 whitespace-nowrap">
                            {{ $log->created_at->format('M d, Y') }}<br>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="font-semibold text-gray-800">
                            {{ $log->user->full_name ?? ($log->user->email ?? 'System') }}
                        </td>
                        <td>
                            <span class="event-badge {{ $log->event }}">{{ $log->event }}</span>
                            <div class="text-xs text-gray-400 mt-1">{{ ucfirst($log->category) }}</div>
                        </td>
                        <td>
                            <span class="text-gray-700">{{ $log->subject_label ?? '—' }}</span>

                            @if(!empty($log->changes))
                                <div>
                                    <button type="button" class="diff-toggle" onclick="toggleDiff({{ $log->activity_log_id }})">
                                        View changes
                                    </button>
                                    <div class="diff-box" id="diff-{{ $log->activity_log_id }}">
                                        @foreach($log->changes as $field => $value)
                                            @if(is_array($value) && array_key_exists('old', $value))
                                                <div class="diff-field">
                                                    <span class="field-name">{{ $field }}:</span>
                                                    <span class="diff-old">{{ $value['old'] ?? 'null' }}</span>
                                                    →
                                                    <span class="diff-new">{{ $value['new'] ?? 'null' }}</span>
                                                </div>
                                            @else
                                                <div class="diff-field">
                                                    <span class="field-name">{{ $field }}:</span>
                                                    {{ is_scalar($value) ? $value : json_encode($value) }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="text-gray-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-400">
                            <i class="fas fa-clipboard-list text-3xl mb-2 block opacity-30"></i>
                            No activity found for this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleDiff(id) {
        document.getElementById('diff-' + id).classList.toggle('open');
    }
</script>
@endsection