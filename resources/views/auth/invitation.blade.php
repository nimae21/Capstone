@extends('layouts.app')
@section('content')
<div style="max-width:580px;margin:60px auto;padding:32px;background:white;border:1px solid #e2e8f0;border-top:4px solid #dc2626;border-radius:20px;box-shadow:0 12px 40px #0f172a10;font-family:Inter,sans-serif;color:#0f172a">
<h1 style="font-size:28px;font-weight:800">Welcome to Achilles.</h1><p>Complete your invited admin account.</p>
@foreach($errors->all() as $error)<p style="color:#991b1b" role="alert">{{ $error }}</p>@endforeach
<form method="POST" action="{{ route('invitation.accept',$token) }}">@csrf
<label>Email<input type="email" value="{{ $invitation->email }}" readonly style="display:block;width:100%;padding:10px;margin:8px 0 16px;background:#f1f5f9;border-radius:8px"></label>
@foreach(['first_name'=>'First name','middle_name'=>'Middle name (optional)','last_name'=>'Last name','suffix'=>'Suffix (optional)'] as $field=>$label)<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field) }}" maxlength="255" @required(in_array($field,['first_name','last_name'])) style="display:block;width:100%;border:1px solid #cbd5e1;padding:10px;border-radius:8px;margin:8px 0 16px"></label>@endforeach
@foreach(['password'=>'Password (at least 8 characters)','password_confirmation'=>'Confirm password'] as $field=>$label)<label>{{ $label }}<input type="password" name="{{ $field }}" required minlength="8" autocomplete="new-password" style="display:block;width:100%;border:1px solid #cbd5e1;padding:10px;border-radius:8px;margin:8px 0 16px"></label>@endforeach
<label><input type="checkbox" name="terms" value="1" required> I accept the store terms and privacy policy.</label><button style="display:block;background:#dc2626;color:white;padding:13px 22px;border-radius:10px;margin-top:24px;width:100%;font-weight:700">Create my admin account</button></form></div>
@endsection
