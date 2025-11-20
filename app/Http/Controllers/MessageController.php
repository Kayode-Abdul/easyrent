<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MessageNotification;

class MessageController extends Controller
{
    public function inbox()
    {
        $messages = Message::where('receiver_id', Auth::user()->user_id)->latest()->get();
        return view('messages.inbox', compact('messages'));
    }

    public function sent()
    {
        $messages = Message::where('sender_id', Auth::user()->user_id)->latest()->get();
        return view('messages.sent', compact('messages'));
    }

    public function compose(Request $request)
    {
        $users = User::where('user_id', '!=', Auth::user()->user_id)->get();
        $to = $request->query('to');
        return view('messages.compose', compact('users', 'to'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => Auth::user()->user_id,
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject ?? 'New Message',
            'body' => $request->body,
        ]);

        // Send email notification to receiver
        try {
            $receiver = User::where('user_id', $request->receiver_id)->first();
            if ($receiver && $receiver->email) {
                Mail::to($receiver->email)->send(new MessageNotification($message));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send message email notification: ' . $e->getMessage());
        }

        return redirect()->route('messages.sent')->with('success', 'Message sent!');
    }

    public function show($id)
    {
        $message = Message::where(function($q) use ($id) {
            $q->where('id', $id)
              ->where(function($q2) {
                  $q2->where('receiver_id', Auth::user()->user_id)
                     ->orWhere('sender_id', Auth::user()->user_id);
              });
        })->firstOrFail();

        if ($message->receiver_id == Auth::user()->user_id && !$message->is_read) {
            $message->is_read = true;
            $message->save();
        }

        return view('messages.show', compact('message'));
    }
}
