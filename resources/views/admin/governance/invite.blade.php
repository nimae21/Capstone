@extends('layouts.admin')
@section('title','Invite admin')
@section('content')
<div class="governance">@include('admin.governance.styles')<h1>Invite an admin</h1><p>Send a private invitation. The recipient completes their own profile and chooses their password.</p><form class="gov-card" style="max-width:620px" method="POST" action="{{ route('admin.users.store-admin') }}">@csrf<div class="gov-field"><label for="email">Admin email address</label><input id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}"></div><p>Invitations expire in 48 hours. Resending replaces the previous link. Existing accounts cannot be invited.</p><button class="gov-button">Send invitation</button></form></div>
@endsection
