@extends('admin.layouts.app')

@section('title', 'Support Tickets')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Support Tickets</h4>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">

            <form method="GET" action="{{ route('admin.support.ticket.index') }}">

                <div class="row">

                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search ticket/user..."
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-control">

                            <option value="">All Status</option>

                            <option value="open"
                                @selected(request('status') === 'open')>
                                Open
                            </option>

                            <option value="in_progress"
                                @selected(request('status') === 'in_progress')>
                                In Progress
                            </option>

                            <option value="pending"
                                @selected(request('status') === 'pending')>
                                Pending
                            </option>

                            <option value="resolved"
                                @selected(request('status') === 'resolved')>
                                Resolved
                            </option>

                            <option value="closed"
                                @selected(request('status') === 'closed')>
                                Closed
                            </option>

                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary">
                            Filter
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- Tickets --}}
    <div class="card">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>
                <tr>
                    <th>#</th>
                    <th>Ticket Number</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Last Message</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>

                @forelse($tickets as $key => $ticket)

                    <tr>

                        {{-- Serial --}}
                        <td>
                            {{ $tickets->firstItem() + $key }}
                        </td>

                        {{-- Ticket Number --}}
                        <td>
                            <span class="fw-bold text-primary">
                                {{ $ticket->ticket_number }}
                            </span>
                        </td>

                        {{-- User --}}
                        <td>

                            <strong>
                                {{ strip_tags($ticket->user->name ?? 'Unknown') }}
                            </strong>

                            <br>

                            <small class="text-muted">
                                {{ strip_tags($ticket->user->email ?? '') }}
                            </small>

                        </td>

                        {{-- Subject --}}
                        <td>
                            {{ $ticket->subject }}
                        </td>

                        {{-- Status --}}
                        <td>

                            @php
                                $badge = match($ticket->status) {
                                    'open' => 'bg-primary',
                                    'in_progress' => 'bg-warning',
                                    'pending' => 'bg-info',
                                    'resolved' => 'bg-success',
                                    'closed' => 'bg-secondary',
                                    default => 'bg-dark',
                                };
                            @endphp

                            <span class="badge {{ $badge }}">
                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                            </span>

                        </td>

                        {{-- Last Message --}}
                        <td>
                            {{ optional($ticket->last_message_at)->format('d M Y h:i A') }}
                        </td>

                        {{-- Action --}}
                        <td>

                            <a href="{{ route(
                                'admin.support.ticket.show',
                                $ticket->id
                            ) }}"
                               class="btn btn-sm btn-primary">

                                Open Chat

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No support tickets found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">

            {{ $tickets->appends(request()->query())->links('admin.components.pagination') }}

        </div>

    </div>

</div>

@endsection