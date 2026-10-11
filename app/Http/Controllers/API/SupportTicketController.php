<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $perPage = max(1, min(
                (int) $request->get('per_page', 10),
                100
            ));

            $query = Ticket::where('user_id', $user->id)
                ->with('latestMessage.attachments');

            if ($request->filled('status')) {
                $status = strtolower($request->status);

                if (in_array($status, [
                    'open',
                    'in_progress',
                    'pending',
                    'resolved',
                    'closed',
                ], true)) {
                    $query->where('status', $status);
                }
            }

            $tickets = $query
                ->orderByRaw('last_message_at IS NULL ASC')
                ->latest('last_message_at')
                ->latest('id')
                ->paginate($perPage);

            $data = collect($tickets->items())->map(
                function ($ticket) {
                    $latestMessage = $ticket->latestMessage;

                    return [
                        'id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'subject' => $ticket->subject,
                        'status' => $ticket->status,
                        'last_message_at' => optional(
                            $ticket->last_message_at
                        )->format('Y-m-d H:i:s'),
                        'created_at' => optional(
                            $ticket->created_at
                        )->format('Y-m-d H:i:s'),

                        'latest_message' => $latestMessage ? [
                            'id' => $latestMessage->id,
                            'message' => $latestMessage->message,
                            'sender_type' => $latestMessage->sender_type,
                            'created_at' => optional(
                                $latestMessage->created_at
                            )->format('Y-m-d H:i:s'),

                            'attachments' => $latestMessage->attachments
                                ->map(fn ($attachment) => [
                                    'id' => $attachment->id,
                                    'file_url' => $attachment->file_url,
                                ])
                                ->values(),
                        ] : null,
                    ];
                }
            )->values();

            return response()->json([
                'status' => true,
                'message' => 'Tickets retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'page' => $tickets->currentPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'last_page' => $tickets->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve tickets.',
            ], 500);
        }
    }

    /**
     * Create a new ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file','max:10240','mimes:jpg,jpeg,png'],
        ]);

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

            $ticket->load('messages.attachments');

            return response()->json([
                'status' => true,
                'message' => 'Support ticket created successfully.',
                'data' => $ticket,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create support ticket.',
            ], 500);
        }
    }

    /**
     * Get ticket details and all messages with attachments
     */

    public function show(Request $request, $ticketId)
    {
        try {
            $ticket = Ticket::where('id', $ticketId)
                ->where('user_id', $request->user()->id)
                ->with([
                    'messages' => function ($query) {
                        $query->oldest('id');
                    },
                    'messages.attachments',
                ])
                ->first();

            if (!$ticket) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Ticket not found.',
                    'data'    => null,
                ], 404);
            }

            $ticketData = $ticket->toArray();

            $ticketData['messages'] = $ticket->messages
                ->map(function ($message) {
                    $messageData = $message->toArray();

                    // Same message-এর সব attachment URL array
                    $messageData['attachments'] = $message->attachments
                        ->map(function ($attachment) {
                            $path = ltrim($attachment->file_path, '/');

                            $path = preg_replace(
                                '#^storage/app/public/#',
                                '',
                                $path
                            );

                            $path = preg_replace('#^storage/#', '', $path);

                            return asset('storage/' . $path);
                        })
                        ->values()
                        ->toArray();

                    return $messageData;
                })
                ->values()
                ->toArray();

            return response()->json([
                'status'  => true,
                'message' => 'Ticket retrieved successfully.',
                'data'    => $ticketData,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Support ticket show error', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to retrieve ticket.',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Send a message to an active ticket
     */
    public function sendMessage(Request $request, $ticketId)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file','max:10240','mimes:jpg,jpeg,png'],
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
            $message = $this->storeMessage(
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

            $message->load('attachments');

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to send message.',
            ], 500);
        }
    }

    /**
     * Store one message and all its attachments.
     */
    private function storeMessage(
        Ticket $ticket,
        $user,
        ?string $message,
        array $attachments = []
    ): TicketMessage {
        // Create the message only once.
        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'sender_type' => 'user',
            'message' => $message,
        ]);

        $storedPaths = [];

        try {
            foreach ($attachments as $file) {
                $path = $file->store(
                    'support/' . $ticket->id,
                    'public'
                );

                if (!$path) {
                    throw new \RuntimeException(
                        'Unable to store support attachment.'
                    );
                }

                $storedPaths[] = $path;

                TicketMessageAttachment::create([
                    'ticket_message_id' => $ticketMessage->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        } catch (\Throwable $e) {
            // Remove files if saving an attachment fails.
            if (!empty($storedPaths)) {
                Storage::disk('public')->delete($storedPaths);
            }

            throw $e;
        }

        return $ticketMessage;
    }
}
