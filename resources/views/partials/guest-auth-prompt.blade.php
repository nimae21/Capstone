@guest
<dialog id="guest-auth-prompt" aria-labelledby="guest-auth-title" style="border:1px solid #e2e8f0;border-radius:22px;padding:32px;max-width:440px;width:calc(100% - 32px);box-shadow:0 24px 80px #0004;font-family:Inter,sans-serif;color:#0f172a">
<h2 id="guest-auth-title" style="font-size:24px;font-weight:800;margin-bottom:12px">Make it yours.</h2><p style="color:#64748b;line-height:1.6;margin-bottom:24px">Log in or create an Achilles account to shop and manage your orders.</p>
<div style="display:flex;gap:10px;flex-wrap:wrap"><a href="{{ route('login') }}" style="background:#dc2626;color:white;padding:12px 20px;border-radius:10px;font-weight:600">Login</a><a href="{{ route('register') }}" style="background:#0f172a;color:white;padding:12px 20px;border-radius:10px;font-weight:600">Register</a><button type="button" id="guest-auth-cancel" style="padding:12px 16px;border:1px solid #cbd5e1;border-radius:10px">Cancel</button></div>
</dialog><style>#guest-auth-prompt::backdrop{background:#0f172a88;backdrop-filter:blur(4px)}</style>
@php($protectedPaths = array_map(fn($name)=>parse_url(route($name),PHP_URL_PATH), ['cart.index','cart.add','checkout.index','profile.index','addresses.index','orders.index']))
<script>
(()=>{const dialog=document.getElementById('guest-auth-prompt');const protectedPaths=@json($protectedPaths);
const protectedUrl=url=>{try{return protectedPaths.some(path=>new URL(url,location.href).pathname===path||new URL(url,location.href).pathname.startsWith(path+'/'));}catch{return false;}};
const prompt=e=>{e.preventDefault();e.stopImmediatePropagation();dialog.showModal();};
document.addEventListener('click',e=>{const link=e.target.closest('a');const button=e.target.closest('button[type="submit"]');if((link&&protectedUrl(link.href))||(button?.form&&protectedUrl(button.form.action)))prompt(e);},true);
document.addEventListener('submit',e=>{if(protectedUrl(e.target.action))prompt(e);},true);
document.getElementById('guest-auth-cancel').addEventListener('click',()=>dialog.close());})();
</script>
@endguest
