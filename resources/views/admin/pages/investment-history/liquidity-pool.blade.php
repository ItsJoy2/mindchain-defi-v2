@extends('admin.layouts.app')

@section('title', 'Liquidity Pool History')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h4 class="mb-0">
                Liquidity Pool History
            </h4>

            <form method="GET"
                  action="{{ route('admin.history.liquidity-pool') }}"
                  class="d-flex gap-2">

                {{-- Search --}}
                <input type="text" name="search" class="form-control" placeholder="Search Username / Email" value="{{ request('search') }}">

                {{-- Wallet --}}
                {{-- <select name="wallet" class="form-select">
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
                </select> --}}

                {{-- Status --}}
                <select name="status" class="form-select">

                    <option value="">All Status</option>

                    <option value="Active"
                        {{ request('status') == 'Active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="Released"
                        {{ request('status') == 'Released' ? 'selected' : '' }}>
                        Released
                    </option>

                    <option value="Cancelled"
                        {{ request('status') == 'Cancelled' ? 'selected' : '' }}>
                        Cancelled
                    </option>

                </select>

                <button type="submit" class="btn btn-primary">
                    Search
                </button>

                <a href="{{ route('admin.history.liquidity-pool') }}"
                   class="btn btn-secondary">
                    Reset
                </a>

            </form>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0 mx-3">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-wrap">User</th>
                            <th class="text-wrap">Wallet</th>
                            <th class="text-wrap">Invested</th>
                            <th class="text-wrap">Reward %</th>
                            <th class="text-wrap">Reward Amount</th>
                            <th class="text-wrap">Total Return</th>
                            <th class="text-wrap">Lock Period</th>
                            <th class="text-wrap">Release At</th>
                            <th class="text-wrap">Status</th>
                            <th class="text-wrap">Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($investments as $key => $investment)

                            <tr>

                                {{-- # --}}
                                <td>
                                    {{ $investments->firstItem() + $key }}
                                </td>

                                {{-- User --}}
                                <td>
                                    <strong>
                                        {{ $investment->user->user_name ?? 'N/A' }}
                                    </strong>

                                    <br>

                                    <small>
                                        {{ $investment->user->email ?? '' }}
                                    </small>
                                </td>

                                {{-- Wallet --}}
                                <td>

                                    @php
                                        $walletClass = match($investment->wallet) {
                                            'MIND' => 'primary',
                                            'MUSD' => 'success',
                                            'BMIND' => 'warning',
                                            'USDT' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $walletClass }}">
                                        {{ $investment->wallet }}
                                    </span>

                                </td>

                                {{-- Invested Amount --}}
                                <td>
                                    <strong>
                                        {{ number_format($investment->invested_amount, 2) }}
                                    </strong>
                                </td>

                                {{-- Reward Percentage --}}
                                <td>
                                    {{ number_format($investment->reward_percentage, 2) }}%
                                </td>

                                {{-- Reward Amount --}}
                                <td>
                                    {{ number_format($investment->reward_amount, 2) }}
                                </td>

                                {{-- Total Return --}}
                                <td>
                                    <strong class="text-success">
                                        {{ number_format($investment->total_return_amount, 2) }}
                                    </strong>
                                </td>

                                {{-- Lock --}}
                                <td>
                                    {{ $investment->lock_days }} Days
                                </td>

                                {{-- Release At --}}
                                <td class="text-wrap">
                                    @if($investment->release_at)
                                        <span class="local-time"
                                            data-time="{{ $investment->release_at->toIso8601String() }}">
                                            {{ $investment->release_at->format('d M Y') }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>

                                    @if($investment->status === 'Active')

                                        <span class="badge bg-info">
                                            Active
                                        </span>

                                    @elseif($investment->status === 'Released')

                                        <span class="badge bg-success">
                                            Released
                                        </span>

                                    @elseif($investment->status === 'Cancelled')

                                        <span class="badge bg-danger">
                                            Cancelled
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ $investment->status }}
                                        </span>

                                    @endif

                                </td>

                                {{-- Created At --}}
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
                                    No Liquidity Pool History Found.
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
