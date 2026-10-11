<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportTicketController extends Controller
{
    /**
     * Ticket dashboard
     */
    public function index(Request $request)
    {
        $query = Ticket::with([
            'user',
            'latestMessage.attachments',
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
            ->orderByRaw('last_message_at IS NULL ASC')
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
                $query->oldest('id');
            },
            'messages.attachments',
        ])->findOrFail($ticketId);

        foreach ($ticket->messages as $message) {
            foreach ($message->attachments as $attachment) {
                $path = ltrim($attachment->file_path, '/');

                // Remove storage/app/public prefix if old records contain it
                $path = preg_replace(
                    '#^storage/app/public/#',
                    '',
                    $path
                );

                $attachment->file_url = asset(
                    'storage/' . $path
                );

                $attachment->file_exists = Storage::disk('public')
                    ->exists($path);

                // Determine image using MIME type or file extension
                $extension = strtolower(
                    pathinfo($attachment->file_name, PATHINFO_EXTENSION)
                );

                $attachment->is_image =
                    str_starts_with($attachment->mime_type ?? '', 'image/')
                    || in_array($extension, [
                        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp',
                    ]);
            }
        }

        return view('admin.pages.support.chat', compact('ticket'));
    }

    /**
     * Admin reply with multiple attachments
     */
    public function reply(Request $request, $ticketId)
    {
        $request->validate([
            'message' => ['nullable','string','max:5000'],
            'attachments' => ['nullable','array','max:5'],
            'attachments.*' => ['file','max:10240','mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt'],
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

        $storedPaths = [];

        try {
            // Create one message for this reply.
            $ticketMessage = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'sender_type' => 'admin',
                'message' => $request->message,
            ]);

            // Store each attachment in the separate attachments table.
            foreach ($request->file('attachments', []) as $file) {
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

            // Remove uploaded files if the database operation fails.
            if (!empty($storedPaths)) {
                Storage::disk('public')->delete($storedPaths);
            }

            report($e);

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
