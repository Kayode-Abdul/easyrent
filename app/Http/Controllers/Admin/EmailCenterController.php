<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmailCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role != 1) {
                abort(403, 'Access denied. Super Admin access required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // Email Statistics
        $stats = [
            'total_users' => User::count(),
            'verified_emails' => User::whereNotNull('email_verified_at')->count(),
            'unverified_emails' => User::whereNull('email_verified_at')->count(),
            'admin_users' => User::where('role', 1)->count(),
            'landlords' => User::where('role', 2)->count(),
            'tenants' => User::where('role', 3)->count(),
            'agents' => User::where('role', 4)->count(),
        ];

        // Recent email campaigns (simulated for demo)
        $recentCampaigns = collect([
            [
                'id' => 1,
                'subject' => 'Welcome to EasyRent Platform',
                'recipients' => 150,
                'sent_at' => now()->subDays(2),
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'subject' => 'Monthly Newsletter - Property Updates',
                'recipients' => 89,
                'sent_at' => now()->subWeek(),
                'status' => 'completed'
            ],
            [
                'id' => 3,
                'subject' => 'System Maintenance Notification',
                'recipients' => 200,
                'sent_at' => now()->subDays(10),
                'status' => 'completed'
            ]
        ]);

        return view('admin.email-center.index', compact('stats', 'recentCampaigns'));
    }

    public function compose()
    {
        $userGroups = [
            'all' => 'All Users (' . User::count() . ')',
            'verified' => 'Verified Users (' . User::whereNotNull('email_verified_at')->count() . ')',
            'unverified' => 'Unverified Users (' . User::whereNull('email_verified_at')->count() . ')',
            'admins' => 'Admin Users (' . User::where('role', 1)->count() . ')',
            'landlords' => 'Landlords (' . User::where('role', 2)->count() . ')',
            'tenants' => 'Tenants (' . User::where('role', 3)->count() . ')',
            'agents' => 'Agents (' . User::where('role', 4)->count() . ')',
        ];

        return view('admin.email-center.compose', compact('userGroups'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipients' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_method' => 'required|in:immediate,scheduled',
            'schedule_date' => 'nullable|required_if:send_method,scheduled|date|after:now'
        ]);

        try {
            $recipients = $this->getRecipients($request->recipients);
            
            if ($recipients->isEmpty()) {
                return redirect()->back()->with('error', 'No recipients found for the selected group.');
            }

            if ($request->send_method === 'immediate') {
                $this->sendImmediateEmails($recipients, $request->subject, $request->message);
                $message = "Email sent successfully to {$recipients->count()} recipients.";
            } else {
                // For demo purposes, we'll just log the scheduled email
                $this->scheduleEmails($recipients, $request->subject, $request->message, $request->schedule_date);
                $message = "Email scheduled successfully for {$recipients->count()} recipients on {$request->schedule_date}.";
            }

            // Log the email campaign
            AuditLog::create([
                'user_id' => optional(auth()->user())->user_id,
                'action' => 'email_campaign_sent',
                'description' => "Sent email campaign '{$request->subject}' to {$recipients->count()} recipients",
                'new_values' => [
                    'subject' => $request->subject,
                    'recipients_count' => $recipients->count(),
                    'send_method' => $request->send_method
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            return redirect()->route('admin.email-center')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function templates()
    {
        $templates = [
            [
                'id' => 1,
                'name' => 'Welcome Email',
                'subject' => 'Welcome to EasyRent Platform',
                'content' => 'Dear {user_name},\n\nWelcome to EasyRent! We\'re excited to have you on board.\n\nBest regards,\nThe EasyRent Team',
                'created_at' => now()->subDays(30)
            ],
            [
                'id' => 2,
                'name' => 'System Maintenance',
                'subject' => 'Scheduled System Maintenance',
                'content' => 'Dear {user_name},\n\nWe will be performing system maintenance on {date}. The platform may be temporarily unavailable.\n\nThank you for your patience.\n\nEasyRent Support Team',
                'created_at' => now()->subDays(15)
            ],
            [
                'id' => 3,
                'name' => 'Monthly Newsletter',
                'subject' => 'EasyRent Monthly Update',
                'content' => 'Dear {user_name},\n\nHere are the latest updates and property listings for this month.\n\n[Content will be customized]\n\nBest regards,\nEasyRent Team',
                'created_at' => now()->subDays(7)
            ]
        ];

        return view('admin.email-center.templates', compact('templates'));
    }

    public function settings()
    {
        $settings = [
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        return view('admin.email-center.settings', compact('settings'));
    }

    private function getRecipients($group)
    {
        $query = User::select('user_id', 'first_name', 'last_name', 'username', 'email');

        switch ($group) {
            case 'all':
                break;
            case 'verified':
                $query->whereNotNull('email_verified_at');
                break;
            case 'unverified':
                $query->whereNull('email_verified_at');
                break;
            case 'admins':
                $query->where('role', 1);
                break;
            case 'landlords':
                $query->where('role', 2);
                break;
            case 'tenants':
                $query->where('role', 3);
                break;
            case 'agents':
                $query->where('role', 4);
                break;
            default:
                return collect();
        }

        return $query->get();
    }

    private function sendImmediateEmails($recipients, $subject, $message)
    {
        foreach ($recipients as $recipient) {
            try {
                // Build display name
                $displayName = trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) ?: ($recipient->username ?? '');

                // Replace placeholders
                $personalizedMessage = str_replace(
                    ['{user_name}', '{user_email}', '{date}'],
                    [$displayName ?: ('User #' . $recipient->user_id), $recipient->email, now()->format('Y-m-d')],
                    $message
                );

                Mail::raw($personalizedMessage, function ($mail) use ($recipient, $subject, $displayName) {
                    $mail->to($recipient->email, $displayName ?: ('User #' . $recipient->user_id))
                         ->subject($subject)
                         ->from(config('mail.from.address'), config('mail.from.name'));
                });

            } catch (\Exception $e) {
                // Log individual email failures but continue with others
                Log::error("Failed to send email to {$recipient->email}: " . $e->getMessage());
            }
        }
    }

    private function scheduleEmails($recipients, $subject, $message, $scheduleDate)
    {
        // In a real application, you would store this in a jobs table or queue
        // For demo purposes, we'll just log it
        Log::info("Email scheduled for {$scheduleDate}: Subject: {$subject}, Recipients: {$recipients->count()}");
    }
}
