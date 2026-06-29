@extends('admin.layouts.app')

@section('title','Dashboard')

@section('content')

<div class="container-fluid">

    <div class="row mb-4">

        <div class="col-lg-8">

            <h2 class="dashboard-title">
                Dashboard
            </h2>

            <p class="dashboard-subtitle mb-0">
                Welcome back 👋 Here is today's platform overview.
            </p>

        </div>

    </div>


    {{-- SUMMARY --}}

    <div class="card summary-card mb-4">

        <div class="card-body p-4">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <h4 class="fw-bold mb-2">
                        Total Registered Users
                    </h4>

                    <p class="mb-0 opacity-75">
                        Overall users registered on the platform.
                    </p>

                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    <div class="summary-value">

                        {{ number_format($DashboardData['totalUsers']) }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- USER STATISTICS --}}

    <h5 class="section-title">
        User Statistics
    </h5>

    <div class="row g-4 mb-5">

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-blue">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Total Users
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['totalUsers']) }}
                            </div>

                        </div>

                        <div class="icon-box bg-blue">
                            <i class="bi bi-people-fill"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-green">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Active Users
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['activeUsers']) }}
                            </div>

                        </div>

                        <div class="icon-box bg-green">
                            <i class="bi bi-person-check-fill"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-orange">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Inactive Users
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['inactiveUsers']) }}
                            </div>

                        </div>

                        <div class="icon-box bg-orange">
                            <i class="bi bi-person-dash-fill"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-red">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="stat-title">
                                Blocked Users
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['blockedUsers']) }}
                            </div>

                        </div>

                        <div class="icon-box bg-red">
                            <i class="bi bi-person-x-fill"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- WALLET DEPOSIT --}}

    <h5 class="section-title">
        Wallet Deposits
    </h5>

    <div class="row g-4">

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-purple">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="stat-title">
                                MIND Deposit
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['mindDeposit'],2) }}
                            </div>

                        </div>

                        <div class="icon-box bg-purple">

                            <i class="bi bi-gem"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-cyan">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="stat-title">
                                MUSD Deposit
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['musdDeposit'],2) }}
                            </div>

                        </div>

                        <div class="icon-box bg-cyan">

                            <i class="bi bi-wallet2"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-indigo">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="stat-title">
                                BMIND Deposit
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['bmindDeposit'],2) }}
                            </div>

                        </div>

                        <div class="icon-box bg-indigo">

                            <i class="bi bi-currency-exchange"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card stat-card border-teal">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="stat-title">
                                USDT Deposit
                            </div>

                            <div class="stat-number">
                                {{ number_format($DashboardData['usdtDeposit'],2) }}
                            </div>

                        </div>

                        <div class="icon-box bg-teal">

                            <i class="bi bi-cash-stack"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
