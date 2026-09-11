@extends('layouts.admin')
@section('title','Accounts')
@section('content')
<div class="governance">@include('admin.governance.styles')<h1>Accounts</h1><p>Manage access while preserving account and order history.</p>
<form class="gov-toolbar gov-card" method="GET"><label for="account-search">Email</label><input id="account-search" type="search" name="search" value="{{ request('search') }}" maxlength="255"><button class="gov-button">Search</button></form>
<div class="gov-card gov-scroll"><table class="gov-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Access</th></tr></thead><tbody>@foreach($users as $user)<tr><td>{{ $user->full_name }}</td><td>{{ $user->email }}</td><td>{{ $user->role }}</td><td>{{ $user->is_active ? 'Active' : 'Suspended' }}</td><td>@if($user->id!==auth()->id() && in_array($user->role,['user','admin']))<form method="POST" action="{{ route('admin.users.toggle-status',$user) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}"><button class="gov-button {{ $user->is_active ? 'secondary' : '' }}">{{ $user->is_active ? 'Suspend' : 'Restore' }}</button></form>@else<span class="gov-tag">Protected</span>@endif</td></tr>@endforeach</tbody></table></div>{{ $users->links() }}</div>
@endsection
