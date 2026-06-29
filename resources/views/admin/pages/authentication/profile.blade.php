@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('content')

<div class="container-fluid">

    @include('admin.components.alerts')

    <div class="row">

        {{-- LEFT CARD --}}
        <div class="col-lg-4">

            <div class="card shadow-sm border-0">

                <div class="card-body text-center">

                    @if($admin->image)

                        <img
                            src="{{ asset($admin->image) }}"
                            id="previewImage"
                            class="rounded-circle shadow mb-3"
                            width="170"
                            height="170"
                            style="object-fit:cover;">

                    @else

                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background=0d6efd&color=fff&size=300"
                            id="previewImage"
                            class="rounded-circle shadow mb-3"
                            width="170"
                            height="170">

                    @endif

                    <h4 class="fw-bold">
                        {{ $admin->name }}
                    </h4>

                    <p class="text-muted mb-1">
                        {{ '@'.$admin->user_name }}
                    </p>

                    <p class="text-muted">
                        {{ $admin->email }}
                    </p>

                    <span class="badge bg-success px-3 py-2">
                        Administrator
                    </span>

                </div>

            </div>

        </div>

        {{-- RIGHT --}}
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header">

                    <h5 class="mb-0">
                        Profile Information
                    </h5>

                </div>

                <div class="card-body">

                    <form
                        action="{{ route('admin.profile.update') }}"
                        method="POST"
                        enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    value="{{ old('name',$admin->name) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="user_name"
                                    value="{{ old('user_name',$admin->user_name) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    name="email"
                                    value="{{ old('email',$admin->email) }}">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Contact
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="contact"
                                    value="{{ old('contact',$admin->contact) }}">

                            </div>

                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Profile Image
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="image"
                                    id="imageInput"
                                    accept="image/*">

                            </div>

                        </div>

                        <button class="btn btn-primary">

                            <i class="fas fa-save me-2"></i>

                            Update Profile

                        </button>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm border-0">

                <div class="card-header">

                    <h5 class="mb-0">
                        Change Password
                    </h5>

                </div>

                <div class="card-body">

                    <form
                        action="{{ route('admin.profile.password') }}"
                        method="POST">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">

                            <label class="form-label">
                                Current Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="current_password">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                New Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password_confirmation">

                        </div>

                        <button class="btn btn-success">

                            <i class="fas fa-key me-2"></i>

                            Change Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


