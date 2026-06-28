@extends('admin.layouts.app')

@section('title', 'Ambassador History')

@section('content')

<div class="row m-0">
    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <strong>Ambassador History</strong>
            </div>

            <div class="card-body">

                <form method="GET" class="mb-4">
                    <div class="row g-2">

                        <div class="col-md-5">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search by Username or Email..."
                                value="{{ request('search') }}"
                            >
                        </div>

                        {{-- <div class="col-md-3">
                            <select name="wallet" class="form-select">
                                <option value="">All Wallets</option>

                                <option value="MIND" {{ request('wallet') == 'MIND' ? 'selected' : '' }}>
                                    MIND
                                </option>

                                <option value="MUSD" {{ request('wallet') == 'MUSD' ? 'selected' : '' }}>
                                    MUSD
                                </option>

                                <option value="BMIND" {{ request('wallet') == 'BMIND' ? 'selected' : '' }}>
                                    BMIND
                                </option>

                                <option value="USDT" {{ request('wallet') == 'USDT' ? 'selected' : '' }}>
                                    USDT
                                </option>
                            </select>
                        </div> --}}

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Search
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a href="{{ route('admin.ambassador-history.index') }}"
                               class="btn btn-secondary w-100">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle transaction-table">

                        <thead>
                            <tr class="text-center text-nowrap">
                                <th>No.</th>
                                <th>User</th>
                                <th>Method / Date</th>
                                <th>Asset / Amount</th>
                                <th>Type / Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>

                        @forelse($histories as $history)

                            @php
                                $type = strtolower($history->type ?? '');

                                $isCredit = in_array($type, [
                                    'credit',
                                    'deposit',
                                    'bonus',
                                    'reward',
                                    'income',
                                    'commission'
                                ]);

                                $typeClass = $isCredit ? 'badge-credit' : 'badge-debit';
                                $amountClass = $isCredit ? 'amount-positive' : 'amount-negative';

                                $status = strtolower($history->status ?? '');

                                if (in_array($status, ['approved','completed','success'])) {
                                    $statusClass = 'badge-approved';
                                } elseif ($status == 'pending') {
                                    $statusClass = 'badge-pending';
                                } else {
                                    $statusClass = 'badge-failed';
                                }
                            @endphp

                            <tr class="text-nowrap">

                                <td class="text-center">
                                    {{ $histories->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div>{{ $history->user_name ?? 'N/A' }}</div>

                                    <div class="user-email">
                                        {{ $history->email ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        {{ $history->method ?? 'SYSTEM' }}
                                    </div>

                                    <div class="date-text local-time" data-time="{{ $history->created_at->toIso8601String() }}">
                                        {{ $history->created_at }}>
                                    </div>
                                </td>

                                <td>
                                    <div>{{ $history->wallet }}</div>

                                    <div class="{{ $amountClass }}">
                                        {{ number_format($history->amount, 3) }}
                                    </div>
                                </td>

                                <td>

                                    <div class="d-flex flex-column">

                                        <span class="custom-badge {{ $typeClass }}">
                                            <span class="badge-dot"></span>
                                            {{ strtoupper($history->type) }}
                                        </span>

                                        <span class="custom-badge {{ $statusClass }}">
                                            {{ strtoupper($history->status) }}
                                        </span>

                                    </div>

                                </td>

                                <td>
                                    {{ $history->description ?: '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No ambassador history found.
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $histories->appends(request()->query())->links('admin.components.pagination') }}
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
