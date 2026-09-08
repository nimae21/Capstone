@extends('layouts.admin')
@section('title', 'Settings')
@section('styles')
<style>
    .settings-page { max-width: 980px; margin: 0 auto; padding: 1rem 0 3rem; }
    .settings-eyebrow { color: #dc2626; font-size: .75rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; margin-bottom: .75rem; }
    .settings-page h1 { font-size: clamp(2rem, 5vw, 3rem); letter-spacing: -.04em; margin-bottom: .6rem; }
    .settings-intro { color: var(--admin-muted); margin-bottom: 2rem; line-height: 1.6; }
    .appearance-card { background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 24px; padding: clamp(1.25rem, 4vw, 2.5rem); box-shadow: 0 12px 40px #00000008; }
    .appearance-heading { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .appearance-icon { width: 52px; height: 52px; display: grid; place-items: center; border-radius: 16px; background: var(--admin-raised); color: #dc2626; font-size: 1.5rem; }
    .appearance-heading h2 { font-size: 1.3rem; margin-bottom: .25rem; }
    .appearance-heading p, .theme-description, .settings-note { color: var(--admin-muted); line-height: 1.6; font-size: .9rem; }
    .theme-control { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; padding: 1.5rem 0; border-top: 1px solid var(--admin-border); }
    #dark-mode-label { display: block; font-weight: 800; font-size: 1.2rem; margin-bottom: .4rem; }
    .theme-switch { width: 64px; height: 36px; border: 2px solid var(--admin-border); border-radius: 999px; padding: 3px; background: #94a3b8; cursor: pointer; flex-shrink: 0; }
    .theme-switch span { display: block; width: 26px; height: 26px; border-radius: 50%; background: #fff; box-shadow: 0 2px 4px #0003; transition: transform .18s; }
    .theme-switch[aria-checked="true"] { background: #dc2626; border-color: #dc2626; }
    .theme-switch[aria-checked="true"] span { transform: translateX(28px); }
    .theme-switch:focus-visible { outline: 3px solid #f87171; outline-offset: 4px; }
    .theme-preview { display: grid; grid-template-columns: 52px 1fr; min-height: 180px; border: 1px solid var(--admin-border); border-radius: 16px; overflow: hidden; margin: 1rem 0; background: var(--admin-background); }
    .preview-sidebar { background: #0a0a0f; padding: 18px 12px; display: flex; flex-direction: column; gap: 14px; }
    .preview-sidebar span { height: 7px; background: #475569; border-radius: 4px; }
    .preview-sidebar span:first-child { background: #dc2626; }
    .preview-content { padding: 22px; }
    .preview-title { width: 40%; height: 10px; background: var(--admin-muted); border-radius: 5px; margin-bottom: 20px; }
    .preview-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .preview-cards span { height: 65px; background: var(--admin-surface); border: 1px solid var(--admin-border); border-radius: 10px; }
    #admin-theme-status { font-size: .9rem; font-weight: 600; margin: 1rem 0 .5rem; }
    @media (prefers-reduced-motion: reduce) { .theme-switch span { transition: none; } }
</style>
@endsection
@section('content')
<div class="settings-page">
    <p class="settings-eyebrow">Your workspace</p>
    <h1>Settings</h1>
    <p class="settings-intro">Make the Achilles admin workspace comfortable for you.</p>
    <section class="appearance-card" aria-labelledby="appearance-title">
        <div class="appearance-heading">
            <span class="appearance-icon"><i class="fas fa-moon" aria-hidden="true"></i></span>
            <div><h2 id="appearance-title">Appearance</h2><p>Choose how your admin pages look.</p></div>
        </div>
        <div class="theme-control">
            <div><span id="dark-mode-label">Dark mode</span><p class="theme-description" id="dark-mode-description">Use darker backgrounds across your dashboard, inventory, orders, and other admin pages.</p></div>
            <button id="admin-dark-mode" type="button" class="theme-switch" role="switch" aria-checked="false" aria-labelledby="dark-mode-label" aria-describedby="dark-mode-description"><span aria-hidden="true"></span></button>
        </div>
        <div class="theme-preview" aria-hidden="true">
            <div class="preview-sidebar"><span></span><span></span><span></span><span></span></div>
            <div class="preview-content"><div class="preview-title"></div><div class="preview-cards"><span></span><span></span><span></span></div></div>
        </div>
        <p id="admin-theme-status" role="status" aria-live="polite"></p>
        <p class="settings-note">Applies immediately and is remembered for your account in this browser. Your storefront appearance stays the same.</p>
        <noscript><p class="settings-note">Enable JavaScript to change the theme.</p></noscript>
    </section>
</div>
@endsection
