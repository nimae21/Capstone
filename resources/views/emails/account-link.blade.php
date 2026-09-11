<div style="font-family:Inter,Arial,sans-serif;background:#f1f5f9;padding:40px;color:#0f172a">
<div style="max-width:540px;margin:auto;background:white;border-radius:20px;padding:32px;border-top:4px solid #dc2626">
<h1>ACHILLES</h1><h2>{{ $invitation ? 'You are invited.' : 'One step to your account.' }}</h2>
<p>{{ $invitation ? 'Choose your own password and complete your admin profile. This invitation expires in 48 hours.' : 'Confirm your email to create your customer account. This link expires in 60 minutes.' }}</p>
<p style="padding:20px 0"><a href="{{ $url }}" style="background:#dc2626;color:white;padding:14px 22px;border-radius:10px;text-decoration:none">{{ $invitation ? 'Accept invitation' : 'Verify email' }}</a></p>
<p>If you did not expect this email, you can ignore it.</p></div></div>
