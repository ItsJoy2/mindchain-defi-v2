<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportTicketController extends Controller
{
    /**
     * Ticket dashboard
     */
    public function index(Request $request)
    {
        $query = Ticket::with([
            'user',
            'latestMessage',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {

                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");

                    });
            });
        }

        $tickets = $query
            ->latest('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.support.index', compact('tickets'));
    }

    /**
     * Chat page
     */
    public function show($ticketId)
    {
        $ticket = Ticket::with([
            'user',
            'messages' => function ($query) {
                $query->oldest();
            },
        ])->findOrFail($ticketId);

        return view(
            'admin.pages.support.chat',
            compact('ticket')
        );
    }

    /**
     * Admin reply
     */
    public function reply(Request $request, $ticketId)
    {
        $request->validate([
            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'attachments' => [
                'nullable',
                'array',
                'max:5',
            ],

            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt',
            ],
        ]);

        if (
            !$request->filled('message') &&
            !$request->hasFile('attachments')
        ) {
            return back()
                ->withErrors([
                    'message' => 'Message or attachment is required.',
                ])
                ->withInput();
        }

        $ticket = Ticket::findOrFail($ticketId);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed.');
        }

        DB::beginTransaction();

        try {

            $attachments = $request->file('attachments', []);

            if (empty($attachments)) {

                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => null,
                    'sender_type' => 'admin',
                    'message' => $request->message,
                ]);

            } else {

                foreach ($attachments as $file) {

                    $path = $file->store(
                        'support/' . $ticket->id,
                        'public'
                    );

                    TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => null,
                        'sender_type' => 'admin',
                        'message' => $request->message,
                        'attachment' => $path,
                        'attachment_name' => $file->getClientOriginalName(),
                        'attachment_type' => $file->getMimeType(),
                        'attachment_size' => $file->getSize(),
                    ]);
                }
            }

            $ticket->update([
                'last_message_at' => now(),
                'status' => 'in_progress',
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Reply sent successfully.'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Unable to send reply.'
            );
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $ticketId)
    {
        $request->validate([
            'status' => [
                'required',
                'in:open,in_progress,pending,resolved,closed',
            ],
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        $ticket->update([
            'status' => $request->status,
        ]);

        return back()->with(
            'success',
            'Ticket status updated successfully.'
        );
    }
}