@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row">

        {{-- Left Side --}}
        <div class="col-lg-4 mb-4">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-body text-center">

                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->full_name) }}&size=180"
                        class="rounded-circle mb-3"
                        width="160">

                   <h4 class="fw-bold">
    {{ auth()->user()->full_name }}
</h4>

                    <p class="text-muted">
                        {{ auth()->user()->email }}
                    </p>

                    @if(auth()->user()->email_verified_at)

                        <span class="badge bg-success">
                            Verified Account
                        </span>

                    @else

                        <span class="badge bg-danger">
                            Email Not Verified
                        </span>

                    @endif

                    <hr>

                    <p class="mb-0">
                        Member since
                    </p>

                    <small class="text-muted">
                        {{ auth()->user()->created_at->format('F d, Y') }}
                    </small>

                </div>

            </div>

        </div>

        {{-- Right Side --}}
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-white">

                    <h4 class="mb-0">
                        Personal Information
                    </h4>

                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('profile.update') }}">

                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label>First Name</label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    value="{{ old('first_name', auth()->user()->first_name) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label>Middle Name</label>

                                <input
                                    type="text"
                                    name="middle_name"
                                    class="form-control"
                                    value="{{ old('middle_name', auth()->user()->middle_name) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label>Last Name</label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    value="{{ old('last_name', auth()->user()->last_name) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label>Suffix</label>

                                <input
                                    type="text"
                                    name="suffix"
                                    class="form-control"
                                    value="{{ old('suffix', auth()->user()->suffix) }}">

                            </div>

                            <div class="col-12 mb-3">

                                <label>Email</label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old('email', auth()->user()->email) }}">

                            </div>

                        </div>

                        <button class="btn btn-dark">
                            Save Changes
                        </button>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm border-0 rounded-4 mt-4">

                <div class="card-header bg-white">

                    <h4 class="mb-0">
                        Change Password
                    </h4>

                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('profile.password') }}">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">

                            <label>Current Password</label>

                            <input
                                type="password"
                                name="current_password"
                                class="form-control">

                        </div>

                        <div class="mb-3">

                            <label>New Password</label>

                            <input
                                type="password"
                                name="password"
                                class="form-control">

                        </div>

                        <div class="mb-3">

                            <label>Confirm Password</label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control">

                        </div>

                        <button class="btn btn-primary">

                            Update Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection