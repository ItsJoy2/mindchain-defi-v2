@extends('admin.layouts.app')

@section('title', 'Transaction History')

@section('content')

<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <strong>Transaction History</strong>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr class="text-center">
                                <th width="70">No.</th>
                                <th>Method / Date</th>
                                <th>Asset / Amount</th>
                                <th>Type / Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($histories as $history)

                                <tr>

                                    <td class="text-center">
                                        {{ $histories->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        <div class="fw-bold">
                                            {{ $history->method ?? 'SYSTEM' }}
                                        </div>

                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y h:i A') }}
                                        </small>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $history->wallet }}
                                        </div>

                                        <div class="text-success">
                                            {{ number_format($history->amount, 8) }}
                                        </div>
                                    </td>

                                    <td>

                                        <div class="mb-1">

                                            @php
                                                $typeColor = match(strtolower($history->type ?? '')) {
                                                    'deposit' => 'success',
                                                    'withdraw' => 'danger',
                                                    'transfer' => 'info',
                                                    'bonus' => 'primary',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-{{ $typeColor }}">
                                                {{ ucfirst($history->type) }}
                                            </span>

                                        </div>

                                        <div>

                                            @php
                                                $statusColor = match(strtolower($history->status ?? '')) {
                                                    'success', 'approved', 'completed' => 'success',
                                                    'pending' => 'warning',
                                                    'failed', 'rejected' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-{{ $statusColor }}">
                                                {{ ucfirst($history->status) }}
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div>
                                            {{ $history->description ?: '-' }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $history->source }}
                                        </small>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        No transaction history found.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">
                    {{ $histories->links('admin.components.pagination') }}
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
