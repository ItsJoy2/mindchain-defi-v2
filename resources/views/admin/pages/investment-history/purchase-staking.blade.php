@extends('admin.layouts.app')

@section('title', 'Purchase Staking History')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h4 class="mb-0">
                    Purchase Staking History
                </h4>

                <form method="GET" class="row g-2">

                    <div class="col-auto">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Username or Email"
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-auto">
                        <select name="wallet" class="form-select">
                            <option value="">All Wallets</option>

                            <option value="MIND"
                                {{ request('wallet') == 'MIND' ? 'selected' : '' }}>
                                MIND
                            </option>

                            <option value="MUSD"
                                {{ request('wallet') == 'MUSD' ? 'selected' : '' }}>
                                MUSD
                            </option>

                            <option value="BMIND"
                                {{ request('wallet') == 'BMIND' ? 'selected' : '' }}>
                                BMIND
                            </option>

                            <option value="USDT"
                                {{ request('wallet') == 'USDT' ? 'selected' : '' }}>
                                USDT
                            </option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-primary">
                            Search
                        </button>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('admin.history.purchase-staking') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </form>

            </div>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0 mx-3">

                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>User</th>
                            <th>Wallet</th>
                            <th>Amount</th>
                            <th>Duration</th>
                            <th>Daily</th>
                            <th>Received Days</th>
                            <th>APY (%)</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($investments as $key => $investment)

                            <tr class="text-nowrap">

                                <td>
                                    {{ $investments->firstItem() + $key }}
                                </td>

                                <td>
                                    <strong>{{ $investment->user->user_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small>{{ $investment->user->email ?? '' }}</small>
                                </td>

                                <td>
                                    {{ ucwords(str_replace('_', ' ', $investment->wallet)) }}
                                </td>

                                <td>
                                    {{ number_format($investment->amount, 2) }}
                                </td>

                                <td>
                                    {{ $investment->duration }} Days
                                </td>

                                <td>
                                    {{ number_format($investment->daily, 2) }}
                                </td>

                                <td>
                                    {{ $investment->received_days }}
                                </td>

                                <td>
                                    {{ number_format($investment->apy_value, 2) }}%
                                </td>

                                <td>
                                    {{ number_format($investment->total_value, 2) }}
                                </td>

                                <td>
                                    @if($investment->status)
                                        <span class="badge bg-info">
                                            Running
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Completed
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="local-time"
                                          data-time="{{ $investment->created_at->toIso8601String() }}">
                                        {{ $investment->created_at }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    No Purchase Staking History Found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="m-3">
            {{ $investments->appends(request()->query())->links('admin.components.pagination') }}
        </div>

    </div>

</div>

@endsection
