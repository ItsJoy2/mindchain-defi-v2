@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')

<div class="row m-2">

    <div class="body flex-grow-1">
        <div class="container-lg px-4">

            @include('admin.components.alerts')

            @yield('content')

        </div>
    </div>

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

                <h5 class="mb-1">{{ $user->name ?: $user->user_name }}</h5>

                <p class="text-muted mb-1">{{ $user->email }}</p>

                @if($user->status)
                    <span class="badge bg-success px-3 py-2">Active</span>
                @else
                    <span class="badge bg-danger px-3 py-2">Inactive</span>
                @endif

                <div class="border rounded p-3 mt-4 text-start">

                    <h6 class="fw-bold mb-3">
                        <i class="cil-shield-alt me-2"></i>
                        Security & Permissions
                    </h6>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>Account Status</span>

                        @if($user->is_block)
                            <span class="badge bg-danger">Blocked</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>Transfer Permission</span>

                        @if($user->transfer_block)
                            <span class="badge bg-danger">Blocked</span>
                        @else
                            <span class="badge bg-success">Allowed</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span>Withdraw Permission</span>

                        @if($user->withdraw_block)
                            <span class="badge bg-danger">Blocked</span>
                        @else
                            <span class="badge bg-success">Allowed</span>
                        @endif
                    </div>

                </div>

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
                            {{ optional($user->sponsor)->user_name ?? 'No Sponsor' }}
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
<div class="card mb-4 m-4">

    <div class="card-header fw-bold">
        Wallet Balances
    </div>

    <div class="card-body">

        <div class="row">

            @foreach($wallets as $wallet => $balance)

            <div class="col-md-3 col-sm-6 mb-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center">

                        <div class="text-muted small mb-1">
                            {{ $wallet }}
                        </div>

                        <h5 class="mb-3 fw-bold">
                            {{ number_format($balance, 3) }}
                        </h5>

                        <button
                            class="btn btn-sm btn-outline-warning wallet-adjust-btn"
                            data-wallet="{{ $wallet }}"
                            data-coreui-toggle="modal"
                            data-coreui-target="#walletAdjustModal"
                        >
                            Wallet Adjust
                        </button>

                    </div>

                </div>

            </div>

            @endforeach

            @include('admin.pages.users.models.__wallet-adjust')

        </div>

    </div>

</div>

{{-- Status --}}
<div class="card m-4">

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



<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;

        background-color: transparent !important;
        border: 1px solid var(--cui-border-color) !important;
        color: var(--cui-body-color) !important;
    }

    .select2-container--default .select2-selection__rendered {
        line-height: 36px !important;
        color: var(--cui-body-color) !important;
    }

    .select2-dropdown {
        background: var(--cui-body-bg) !important;
        border: 1px solid var(--cui-border-color) !important;
    }

    .select2-search__field {
        background: transparent !important;
        color: var(--cui-body-color) !important;
    }
</style>


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


    // wallet adjust button click event
    $(document).on('click', '.wallet-adjust-btn', function () {

        let wallet = $(this).data('wallet');

        $('#walletName').val(wallet);
        $('#walletDisplay').val(wallet);

        if (wallet === 'AMBASSADOR') {
            $('.modal-title').text('Adjust Ambassador Wallet');
        } else {
            $('.modal-title').text('Adjust ' + wallet + ' Wallet');
        }
    });
</script>
@endsection

