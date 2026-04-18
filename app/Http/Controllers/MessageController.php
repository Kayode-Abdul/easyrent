<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MessageNotification;
use App\Models\Apartment;
use App\Models\Property;
use App\Models\ArtisanTask;
use App\Models\ArtisanBid;
use Illuminate\Support\Facades\Log;

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
        $user = Auth::user();

        // Define users the current user has encountered
        $encounteredUserIds = collect();

        if ($user->isTenant()) {
            // My Landlords
            $landlordIds = Apartment::where('tenant_id', $user->user_id)->pluck('user_id');
            $encounteredUserIds = $encounteredUserIds->merge($landlordIds);

            // My Assigned Agents
            $agentIds = Property::whereIn('property_id', Apartment::where('tenant_id', $user->user_id)->pluck('property_id'))->pluck('agent_id');
            $encounteredUserIds = $encounteredUserIds->merge($agentIds);
        }

        if ($user->isLandlord()) {
            // My Tenants
            $tenantIds = Apartment::where('user_id', $user->user_id)->pluck('tenant_id');
            $encounteredUserIds = $encounteredUserIds->merge($tenantIds);

            // My Agents
            $agentIds = Property::where('user_id', $user->user_id)->pluck('agent_id');
            $encounteredUserIds = $encounteredUserIds->merge($agentIds);
        }

        if ($user->isAgent()) {
            // Landlords I work for
            $landlordIds = Property::where('agent_id', $user->user_id)->pluck('user_id');
            $encounteredUserIds = $encounteredUserIds->merge($landlordIds);

            // Tenants in properties I manage
            $tenantIds = Apartment::whereIn('property_id', Property::where('agent_id', $user->user_id)->pluck('property_id'))->pluck('tenant_id');
            $encounteredUserIds = $encounteredUserIds->merge($tenantIds);
        }

        if ($user->isArtisan()) {
            // Landlords/Tenants who hired me
            $clientIds = ArtisanTask::whereIn('id', ArtisanBid::where('artisan_id', $user->user_id)->where('status', 'accepted')->pluck('task_id'))
                ->get()
                ->flatMap(fn($t) => [$t->landlord_id, $t->tenant_id]);
            $encounteredUserIds = $encounteredUserIds->merge($clientIds);
        }

        // Landlords/Tenants can also message artisans they've hired
        $artisanIds = ArtisanBid::where('status', 'accepted')
            ->whereIn('task_id', ArtisanTask::where('landlord_id', $user->user_id)->orWhere('tenant_id', $user->user_id)->pluck('id'))
            ->pluck('artisan_id');
        $encounteredUserIds = $encounteredUserIds->merge($artisanIds);

        // Filter and get users
        $userIds = $encounteredUserIds->unique()->filter()->reject(fn($id) => $id == $user->user_id);

        if ($user->admin) {
            $users = User::where('user_id', '!=', $user->user_id)->get();
        }
        else {
            $users = User::whereIn('user_id', $userIds)->get();
        }
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
        }
        catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to send message email notification: ' . $e->getMessage());
        }

        return redirect()->route('messages.sent')->with('success', 'Message sent!');
    }

    public function show($id)
    {
        $message = Message::where(function ($q) use ($id) {
            $q->where('id', $id)
                ->where(function ($q2) {
                $q2->where('receiver_id', Auth::user()->user_id)
                    ->orWhere('sender_id', Auth::user()->user_id);
            }
            );
        })->firstOrFail();

        if ($message->receiver_id == Auth::user()->user_id && !$message->is_read) {
            $message->is_read = true;
            $message->save();
        }

        return view('messages.show', compact('message'));
    }
}