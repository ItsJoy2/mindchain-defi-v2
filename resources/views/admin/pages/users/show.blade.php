@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')

<div class="row">

    {{-- Profile --}}
    <div class="col-lg-4">

        <div class="card mb-4">

            <div class="card-body text-center">

                @if($user->image)
                    <img
                        src="{{ asset($user->image) }}"
                        class="rounded-circle mb-3"
                        style="width:120px;height:120px;object-fit:cover;"
                    >
                @else
                    <div
                        class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width:120px;height:120px;font-size:40px;"
                    >
                        {{ strtoupper(substr($user->user_name,0,1)) }}
                    </div>
                @endif

                <h5 class="mb-1">
                    {{ $user->name ?: $user->user_name }}
                </h5>

                <p class="text-muted mb-1">
                    {{ $user->email }}
                </p>

                <p class="text-muted">
                    {{ $user->contact ?: 'No Contact' }}
                </p>

                @if($user->status)
                    <span class="badge bg-success">
                        Active
                    </span>
                @else
                    <span class="badge bg-danger">
                        Inactive
                    </span>
                @endif

            </div>

        </div>

    </div>

    {{-- Details --}}
    <div class="col-lg-8">

        <div class="card mb-4">

            <div class="card-header fw-bold">
                User Information
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <strong>User Name</strong>
                        <div>{{ $user->user_name }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Full Name</strong>
                        <div>{{ $user->name ?: 'N/A' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Email</strong>
                        <div>{{ $user->email }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Contact</strong>
                        <div>{{ $user->contact ?: '-' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Gender</strong>
                        <div>{{ $user->gender ?: '-' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Date Of Birth</strong>
                        <div>{{ $user->date_of_birth ?: '-' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Country</strong>
                        <div>{{ $user->country ?: '-' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>City</strong>
                        <div>{{ $user->city ?: '-' }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Referral Code</strong>
                        <div>{{ $user->referral_code }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Sponsor</strong>
                        <div>
                            {{ optional($user->sponsor)->user_name ?? 'Root User' }}
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <strong>Joined At</strong>
                        <div>
                            {{ $user->created_at->format('d M Y h:i A') }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Actions</strong>

                        <div class="d-flex gap-2">

                            <button
                                class="btn btn-warning btn-sm"
                                data-coreui-toggle="modal"
                                data-coreui-target="#editUserModal"
                            >
                                <i class="cil-pen me-1"></i>
                                Edit User
                            </button>

                            <button
                                class="btn btn-danger btn-sm"
                                data-coreui-toggle="modal"
                                data-coreui-target="#changePasswordModal"
                            >
                                <i class="cil-lock-locked me-1"></i>
                                Change Password
                            </button>

                        </div>

                        @include('admin.pages.users.models.__update')
                        @include('admin.pages.users.models.__password-update')
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- Wallets --}}
<div class="card mb-4">

    <div class="card-header fw-bold">
        Wallet Balances
    </div>

    <div class="card-body">

        <div class="row">

            @foreach($wallets as $wallet => $balance)

                <div class="col-md-3 mb-3">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <div class="text-muted small">
                                {{ $wallet }}
                            </div>

                            <h5 class="mb-0">
                                {{ number_format($balance, 3) }}
                            </h5>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

{{-- Status --}}
<div class="card">

    <div class="card-header fw-bold">
        Account Status
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-2 mb-3">
                KYC
                <br>
                {!! $user->kyc
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-danger">Pending</span>' !!}
            </div>

            <div class="col-md-2 mb-3">
                Merchant
                <br>
                {!! $user->merchant_status
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>' !!}
            </div>

            <div class="col-md-2 mb-3">
                Consultant
                <br>
                {!! $user->consultant
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>' !!}
            </div>

            <div class="col-md-2 mb-3">
                Ambassador
                <br>
                {!! $user->ambassador
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>' !!}
            </div>

            <div class="col-md-2 mb-3">
                Elite Club
                <br>
                {!! $user->elite_club
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>' !!}
            </div>

            <div class="col-md-2 mb-3">
                Angel Club
                <br>
                {!! $user->angel_club
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>' !!}
            </div>

        </div>

    </div>

</div>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $('#sponsorSelect').select2({
        dropdownParent: $('#editUserModal'),
        placeholder: 'Search sponsor...',
        minimumInputLength: 2,

        ajax: {
            url: "{{ route('admin.users.search') }}",
            dataType: 'json',

            data: function(params) {
                return {
                    q: params.term,
                    user_id: "{{ $user->id }}"
                };
            },

            processResults: function(data) {
                return data;
            }
        }
    });
</script>
@endsection

