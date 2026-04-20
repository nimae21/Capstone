@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card">

                <div class="card-header">
                    {{ __('Verify Your Email Address') }}
                </div>

                <div class="card-body">

                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <p>
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                    </p>

                    <p>
                        {{ __('If you did not receive the email') }},
                    </p>

                    <!-- Resend button -->
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">
                            {{ __('click here to request another') }}
                        </button>
                    </form>

                    <hr>

                    <!-- NAVIGATION FIX -->
                    <div style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">

                        <!-- Back to login -->
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                            ← Back to Login
                        </a>

                        <!-- Back to home -->
                        <a href="{{ route('home') }}" class="btn btn-primary btn-sm">
                            Go to Home
                        </a>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                Logout
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection