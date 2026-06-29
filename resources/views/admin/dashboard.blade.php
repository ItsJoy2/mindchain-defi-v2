@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-lg px-4">

    {{-- Users --}}
    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fs-2 fw-bold">
                            {{ number_format($DashboardData['totalUsers']) }}
                        </div>

                        <div class="opacity-75">
                            Total Users
                        </div>
                    </div>

                    <div class="display-5 opacity-50">
                        <i class="fas fa-users"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fs-2 fw-bold">
                            {{ number_format($DashboardData['activeUsers']) }}
                        </div>

                        <div class="opacity-75">
                            Active Users
                        </div>
                    </div>

                    <div class="display-5 opacity-50">
                        <i class="fas fa-user-check"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fs-2 fw-bold">
                            {{ number_format($DashboardData['inactiveUsers']) }}
                        </div>

                        <div class="opacity-75">
                            Inactive Users
                        </div>
                    </div>

                    <div class="display-5 opacity-50">
                        <i class="fas fa-user-clock"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fs-2 fw-bold">
                            {{ number_format($DashboardData['blockedUsers']) }}
                        </div>

                        <div class="opacity-75">
                            Blocked Users
                        </div>
                    </div>

                    <div class="display-5 opacity-50">
                        <i class="fas fa-user-lock"></i>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- Wallet Deposits --}}
    <div class="row g-4">

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-body-secondary">
                                MIND Deposit
                            </small>

                            <h4 class="mt-2 mb-0 fw-bold text-primary">
                                {{ number_format($DashboardData['mindDeposit'],2) }}
                            </h4>

                        </div>

                        <div>
                            @if(isset($walletIcons['MIND']))
                                <img src="{{ asset($walletIcons['MIND']) }}"
                                    alt="MIND"
                                    width="50"
                                    height="50"
                                    class="opacity-75">
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-body-secondary">
                                MUSD Deposit
                            </small>

                            <h4 class="mt-2 mb-0 fw-bold text-info">
                                {{ number_format($DashboardData['musdDeposit'],2) }}
                            </h4>

                        </div>

                        <div>
                            @if(isset($walletIcons['MUSD']))
                                <img src="{{ asset($walletIcons['MUSD']) }}"
                                    alt="MUSD"
                                    width="50"
                                    height="50"
                                    class="opacity-75">
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-body-secondary">
                                BMIND Deposit
                            </small>

                            <h4 class="mt-2 mb-0 fw-bold text-warning">
                                {{ number_format($DashboardData['bmindDeposit'],2) }}
                            </h4>

                        </div>

                        <div>
                            @if(isset($walletIcons['BMIND']))
                                <img src="{{ asset($walletIcons['BMIND']) }}"
                                    alt="BMIND"
                                    width="50"
                                    height="50"
                                    class="opacity-75">
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-body-secondary">
                                USDT Deposit
                            </small>

                            <h4 class="mt-2 mb-0 fw-bold text-success">
                                {{ number_format($DashboardData['usdtDeposit'],2) }}
                            </h4>

                        </div>

                        <div>
                            @if(isset($walletIcons['USDT']))
                                <img src="{{ asset($walletIcons['USDT']) }}"
                                    alt="USDT"
                                    width="50"
                                    height="50"
                                    class="opacity-75">
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

@endsection
