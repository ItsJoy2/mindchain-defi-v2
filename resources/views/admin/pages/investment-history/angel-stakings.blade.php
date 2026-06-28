@extends('admin.layouts.app')

@section('title', 'Angel Staking History')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                Angel Staking History
            </h4>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0 mx-3">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Duration</th>
                            <th>Daily Bonus</th>
                            <th>Received Days</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($investments as $key => $investment)

                            <tr>

                                <td>
                                    {{ $investments->firstItem() + $key }}
                                </td>

                                <td>
                                    <strong>{{ $investment->user->user_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small>{{ $investment->user->email ?? '' }}</small>
                                </td>

                                <td>
                                    {{ number_format($investment->amount, 2) }}
                                </td>

                                <td>
                                    {{ $investment->duration }} Days
                                </td>

                                <td>
                                    {{ number_format($investment->daily_bonus, 2) }}
                                </td>

                                <td>
                                    {{ $investment->received_days }}
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
                                <td colspan="8" class="text-center py-4">
                                    No Investment History Found.
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
