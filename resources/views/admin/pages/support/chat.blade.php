@extends('admin.layouts.app')

@section('title', 'Support Chat')

@section('content')

<div class="container-fluid">

    <div class="row">

        {{-- Chat --}}
        <div class="col-lg-9">

            <div class="card">

                {{-- Header --}}
                <div class="card-header">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h5 class="mb-0">
                                {{ $ticket->subject }}
                            </h5>

                            <small class="text-muted">
                                {{ strip_tags($ticket->user->name ?? 'Unknown') }}
                                -
                                {{ strip_tags($ticket->user->email ?? '') }}
                            </small>

                        </div>

                        <div>

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

                        </div>

                    </div>

                </div>

                {{-- Messages --}}
                <div class="card-body"id="chatMessages"style="height: 500px;overflow-y: auto;">

                    @foreach($ticket->messages as $message)

                        @if($message->sender_type === 'admin')

                            {{-- Admin message --}}

                            <div class="d-flex justify-content-end mb-3">

                                <div style="max-width:70%;">

                                    <div class="bg-primary text-white rounded p-3">

                                        @if($message->message)
                                            <div>
                                                {!! nl2br(e($message->message)) !!}
                                            </div>
                                        @endif

                                        @if($message->attachment)

                                            <div class="mt-2">

                                                @if(
                                                    str_starts_with(
                                                        $message->attachment_type ?? '',
                                                        'image/'
                                                    )
                                                )

                                                    <a href="{{ $message->attachment_url }}"
                                                       target="_blank">

                                                        <img
                                                            src="{{ $message->attachment_url }}"
                                                            class="img-fluid rounded"
                                                            style="max-width:250px;"
                                                        >

                                                    </a>

                                                @else

                                                    <a
                                                        href="{{ $message->attachment_url }}"
                                                        target="_blank"
                                                        class="text-white"
                                                    >
                                                        📎
                                                        {{ $message->attachment_name }}
                                                    </a>

                                                @endif

                                            </div>

                                        @endif

                                    </div>

                                    <small class="text-muted float-end mt-1">

                                        {{ $message->created_at->format('d M Y h:i A') }}

                                    </small>

                                </div>

                            </div>

                        @else

                            {{-- User message --}}

                            <div class="d-flex justify-content-start mb-3">

                                <div style="max-width:70%;">

                                    <div class=" border rounded p-3">

                                        @if($message->message)

                                            <div>
                                                {!! nl2br(e($message->message)) !!}
                                            </div>

                                        @endif

                                        @if($message->attachment)

                                            <div class="mt-2">

                                                @if(
                                                    str_starts_with(
                                                        $message->attachment_type ?? '',
                                                        'image/'
                                                    )
                                                )

                                                    <a
                                                        href="{{ $message->attachment_url }}"
                                                        target="_blank"
                                                    >

                                                        <img
                                                            src="{{ $message->attachment_url }}"
                                                            class="img-fluid rounded"
                                                            style="max-width:250px;"
                                                        >

                                                    </a>

                                                @else

                                                    <a
                                                        href="{{ $message->attachment_url }}"
                                                        target="_blank"
                                                    >
                                                        📎
                                                        {{ $message->attachment_name }}
                                                    </a>

                                                @endif

                                            </div>

                                        @endif

                                    </div>

                                    <small class="text-muted">

                                        {{ $message->created_at->format('d M Y h:i A') }}

                                    </small>

                                </div>

                            </div>

                        @endif

                    @endforeach

                </div>

                {{-- Reply --}}
                @if($ticket->status !== 'closed')

                    <div class="card-footer">

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.support.ticket.reply',
                                $ticket->id
                            ) }}"
                            enctype="multipart/form-data"
                        >

                            @csrf

                            <textarea
                                name="message"
                                class="form-control mb-2"
                                rows="3"
                                placeholder="Write your reply..."
                            ></textarea>

                            <div class="d-flex justify-content-between">

                                <input
                                    type="file"
                                    name="attachments[]"
                                    class="form-control"
                                    multiple
                                    accept="
                                        image/*,
                                        .pdf,
                                        .doc,
                                        .docx,
                                        .xls,
                                        .xlsx,
                                        .txt
                                    "
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary ms-2"
                                >
                                    Send
                                </button>

                            </div>

                        </form>

                    </div>

                @endif

            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-lg-3">

            <div class="card">

                <div class="card-header">
                    Ticket Status
                </div>

                <div class="card-body">

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.support.ticket.status',
                            $ticket->id
                        ) }}"
                    >

                        @csrf

                        <select
                            name="status"
                            class="form-control mb-3"
                        >

                            <option value="open"
                                @selected($ticket->status === 'open')>
                                Open
                            </option>

                            <option value="in_progress"
                                @selected($ticket->status === 'in_progress')>
                                In Progress
                            </option>

                            <option value="pending"
                                @selected($ticket->status === 'pending')>
                                Pending
                            </option>

                            <option value="resolved"
                                @selected($ticket->status === 'resolved')>
                                Resolved
                            </option>

                            <option value="closed"
                                @selected($ticket->status === 'closed')>
                                Closed
                            </option>

                        </select>

                        <button class="btn btn-success w-100">
                            Update Status
                        </button>

                    </form>

                </div>

            </div>

            <div class="card mt-3">

                <div class="card-header">
                    User Information
                </div>

                <div class="card-body">

                    <strong>
                        {{ strip_tags($ticket->user->name ?? 'N/A') }}
                    </strong>

                    <br>

                    <small>
                        {{ strip_tags($ticket->user->email ?? 'N/A') }}
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const chat = document.getElementById('chatMessages');

        if (chat) {
            chat.scrollTop = chat.scrollHeight;
        }

    });
</script>

@endpush