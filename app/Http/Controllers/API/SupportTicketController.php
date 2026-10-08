<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupportTicketController extends Controller
{
    /**
     * Get user's tickets
     */
    public function index(Request $request)
    {
        try {

            $user = $request->user();

            $perPage = (int) $request->get('per_page', 10);

            $query = Ticket::where('user_id', $user->id)
                ->with('latestMessage');

            if ($request->filled('status')) {

                switch (strtolower($request->status)) {

                    case 'open':
                        $query->where('status', 'open');
                        break;

                    case 'in_progress':
                        $query->where('status', 'in_progress');
                        break;

                    case 'pending':
                        $query->where('status', 'pending');
                        break;

                    case 'resolved':
                        $query->where('status', 'resolved');
                        break;

                    case 'closed':
                        $query->where('status', 'closed');
                        break;
                }
            }

            $tickets = $query
                ->latest('last_message_at')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Tickets retrieved successfully',

                'data' => collect($tickets->items())->map(function ($ticket) {

                    return [
                        'id'              => $ticket->id,
                        'ticket_number'   => $ticket->ticket_number,
                        'subject'         => $ticket->subject,
                        'status'          => $ticket->status,
                        'last_message_at' => optional($ticket->last_message_at)
                            ->format('Y-m-d H:i:s'),
                        'created_at'      => optional($ticket->created_at)
                            ->format('Y-m-d H:i:s'),

                        'latest_message' => $ticket->latestMessage ? [
                            'id'         => $ticket->latestMessage->id,
                            'message'    => $ticket->latestMessage->message,
                            'sender_type'=> $ticket->latestMessage->sender_type,
                            'created_at' => optional($ticket->latestMessage->created_at)
                                ->format('Y-m-d H:i:s'),
                        ] : null,
                    ];

                })->values(),

                'pagination' => [
                    'page'     => $tickets->currentPage(),
                    'per_page' => $tickets->perPage(),
                    'total'    => $tickets->total(),
                ]

            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => [ 'required', 'string', 'max:255'],
            'message' => [ 'required', 'string', 'max:5000'],
            'attachments' => [ 'nullable', 'array', 'max:5' ],
            'attachments.*' => [ 'file', 'max:10240', 'mimes:jpg,jpeg,png']]);

        $user = $request->user();

        DB::beginTransaction();

        try {

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'subject' => $request->subject,
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $this->storeMessage(
                ticket: $ticket,
                user: $user,
                message: $request->message,
                attachments: $request->file('attachments', [])
            );

            DB::commit();

            $ticket->load('messages');

            return response()->json([
                'status' => true,
                'message' => 'Support ticket created successfully.',
                'data' => $ticket,
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Unable to create support ticket.',
            ], 500);
        }
    }

    public function show(Request $request, $ticketId)
    {
        $ticket = Ticket::where('id', $ticketId)
            ->where('user_id', $request->user()->id)
            ->with([
                'messages' => function ($query) {
                    $query->oldest();
                }
            ])
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Ticket retrieved successfully.',
            'data' => $ticket,
        ]);
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $ticketId)
    {
        $request->validate([
            'message' => ['nullable','string','max:5000',],
            'attachments' => ['nullable','array','max:5',],
            'attachments.*' => ['file','max:10240','mimes:jpg,jpeg,png',],
        ]);

        if (
            !$request->filled('message') &&
            !$request->hasFile('attachments')
        ) {
            throw ValidationException::withMessages([
                'message' => 'Message or attachment is required.',
            ]);
        }

        $ticket = Ticket::where('id', $ticketId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($ticket->status === 'closed') {
            return response()->json([
                'status' => false,
                'message' => 'This ticket is already closed.',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $this->storeMessage(
                ticket: $ticket,
                user: $request->user(),
                message: $request->message,
                attachments: $request->file('attachments', [])
            );

            $ticket->update([
                'last_message_at' => now(),
                'status' => 'open',
            ]);

            DB::commit();

            $message = $ticket->messages()
                ->latest()
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Unable to send message.',
            ], 500);
        }
    }

    /**
     * Close ticket
     */
    // public function close(Request $request, $ticketId)
    // {
    //     $ticket = Ticket::where('id', $ticketId)
    //         ->where('user_id', $request->user()->id)
    //         ->first();

    //     if (!$ticket) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Ticket not found.',
    //         ], 404);
    //     }

    //     $ticket->update([
    //         'status' => 'closed',
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Ticket closed successfully.',
    //         'data' => $ticket,
    //     ]);
    // }

    /**
     * Store message and attachments
     */
    private function storeMessage(
        Ticket $ticket,
        $user,
        ?string $message,
        array $attachments = []
    ): void {

        if (empty($attachments)) {

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'sender_type' => 'user',
                'message' => $message,
            ]);

            return;
        }

        foreach ($attachments as $file) {

            $path = $file->store(
                'support/' . $ticket->id,
                'public'
            );

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'sender_type' => 'user',
                'message' => $message,
                'attachment' => $path,
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_type' => $file->getMimeType(),
                'attachment_size' => $file->getSize(),
            ]);
        }
    }
}
