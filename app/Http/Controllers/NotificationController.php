<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get real-time notifications for the authenticated user
     */
    public function getNotifications(Request $request)
    {
        $user = auth()->user();
        $notifications = [];

        // Get role-specific notifications
        switch ($user->role) {
            case 1: // Admin
                $notifications = $this->getAdminNotifications($user);
                break;
            case 2: // Landlord
                $notifications = $this->getLandlordNotifications($user);
                break;
            case 3: // Tenant
                $notifications = $this->getTenantNotifications($user);
                break;
            case 4: // Agent
                $notifications = $this->getAgentNotifications($user);
                break;
            case 5: // Marketer
                $notifications = $this->getMarketerNotifications($user);
                break;
            case 6: // Regional Manager
                $notifications = $this->getRegionalManagerNotifications($user);
                break;
        }

        // Add common notifications
        $commonNotifications = $this->getCommonNotifications($user);
        $notifications = array_merge($notifications, $commonNotifications);

        // Sort by priority and timestamp
        usort($notifications, function($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            }
            return $this->getPriorityValue($b['priority']) - $this->getPriorityValue($a['priority']);
        });

        return response()->json([
            'success' => true,
            'notifications' => array_slice($notifications, 0, 20), // Limit to 20 most recent
            'unread_count' => count(array_filter($notifications, function($n) { return !$n['read']; }))
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        $userId = auth()->user()->user_id;

        // Create notifications table if it doesn't exist
        if (!DB::getSchemaBuilder()->hasTable('user_notifications')) {
            DB::statement("
                CREATE TABLE user_notifications (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    notification_id VARCHAR(255) NOT NULL,
                    type VARCHAR(100) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    data JSON NULL,
                    read_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_notification (user_id, notification_id)
                )
            ");
        }

        try {
            DB::table('user_notifications')
                ->where('user_id', $userId)
                ->where('notification_id', $notificationId)
                ->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get admin notifications
     */
    private function getAdminNotifications($user)
    {
        $notifications = [];

        // System alerts
        $failedPayments = Payment::where('status', 'failed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($failedPayments > 0) {
            $notifications[] = [
                'id' => 'failed_payments_' . date('Y-m-d'),
                'type' => 'system_alert',
                'title' => 'Failed Payments Alert',
                'message' => "{$failedPayments} payment(s) failed in the last 24 hours",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/admin/payments?status=failed',
                'icon' => 'nc-icon nc-money-coins text-danger'
            ];
        }

        // New user registrations
        $newUsers = User::where('created_at', '>=', now()->subHours(24))->count();
        if ($newUsers > 0) {
            $notifications[] = [
                'id' => 'new_users_' . date('Y-m-d'),
                'type' => 'user_activity',
                'title' => 'New User Registrations',
                'message' => "{$newUsers} new user(s) registered in the last 24 hours",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/admin/users',
                'icon' => 'nc-icon nc-single-02 text-success'
            ];
        }

        // System health alerts
        $systemHealth = $this->checkSystemHealth();
        if (!$systemHealth['healthy']) {
            $notifications[] = [
                'id' => 'system_health_' . date('Y-m-d-H'),
                'type' => 'system_alert',
                'title' => 'System Health Warning',
                'message' => $systemHealth['message'],
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/admin/system-health',
                'icon' => 'nc-icon nc-settings-gear-65 text-warning'
            ];
        }

        return $notifications;
    }

    /**
     * Get landlord notifications
     */
    private function getLandlordNotifications($user)
    {
        $notifications = [];

        // New bookings
        $properties = Property::where('user_id', $user->user_id)->pluck('prop_id');
        $newBookings = DB::table('bookings')
            ->whereIn('property_id', $properties)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($newBookings > 0) {
            $notifications[] = [
                'id' => 'new_bookings_' . $user->user_id . '_' . date('Y-m-d'),
                'type' => 'booking',
                'title' => 'New Booking Requests',
                'message' => "You have {$newBookings} new booking request(s)",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/bookings',
                'icon' => 'nc-icon nc-bookmark-2 text-primary'
            ];
        }

        // Payment notifications
        $recentPayments = Payment::where('landlord_id', $user->user_id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($recentPayments > 0) {
            $totalAmount = Payment::where('landlord_id', $user->user_id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subHours(24))
                ->sum('amount');

            $notifications[] = [
                'id' => 'payments_received_' . $user->user_id . '_' . date('Y-m-d'),
                'type' => 'payment',
                'title' => 'Payments Received',
                'message' => "You received ₦" . number_format($totalAmount, 2) . " from {$recentPayments} payment(s)",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/payments',
                'icon' => 'nc-icon nc-money-coins text-success'
            ];
        }

        // Expiring leases
        $expiringLeases = Apartment::whereIn('property_id', $properties)
            ->where('range_end', '<=', now()->addDays(30))
            ->where('range_end', '>=', now())
            ->count();

        if ($expiringLeases > 0) {
            $notifications[] = [
                'id' => 'expiring_leases_' . $user->user_id . '_' . date('Y-m'),
                'type' => 'lease_expiry',
                'title' => 'Leases Expiring Soon',
                'message' => "{$expiringLeases} lease(s) will expire within 30 days",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/myproperty',
                'icon' => 'nc-icon nc-time-alarm text-warning'
            ];
        }

        return $notifications;
    }

    /**
     * Get tenant notifications
     */
    private function getTenantNotifications($user)
    {
        $notifications = [];

        // Pending payments
        $pendingPayments = Payment::where('tenant_id', $user->user_id)
            ->where('status', 'pending')
            ->count();

        if ($pendingPayments > 0) {
            $notifications[] = [
                'id' => 'pending_payments_' . $user->user_id,
                'type' => 'payment_reminder',
                'title' => 'Pending Payments',
                'message' => "You have {$pendingPayments} pending payment(s)",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/payments',
                'icon' => 'nc-icon nc-money-coins text-warning'
            ];
        }

        // Lease expiry reminders
        $myApartments = Apartment::where('tenant_id', $user->user_id)
            ->where('range_end', '<=', now()->addDays(30))
            ->where('range_end', '>=', now())
            ->count();

        if ($myApartments > 0) {
            $notifications[] = [
                'id' => 'lease_expiry_' . $user->user_id . '_' . date('Y-m'),
                'type' => 'lease_expiry',
                'title' => 'Lease Expiring Soon',
                'message' => "Your lease will expire within 30 days",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/myproperty',
                'icon' => 'nc-icon nc-time-alarm text-danger'
            ];
        }

        return $notifications;
    }

    /**
     * Get agent notifications
     */
    private function getAgentNotifications($user)
    {
        $notifications = [];

        // Properties assigned to manage
        $assignedProperties = Property::where('agent_id', $user->user_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($assignedProperties > 0) {
            $notifications[] = [
                'id' => 'assigned_properties_' . $user->user_id . '_' . date('Y-m-d'),
                'type' => 'assignment',
                'title' => 'New Property Assignments',
                'message' => "You have been assigned to manage {$assignedProperties} new property(ies)",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/myproperty',
                'icon' => 'nc-icon nc-istanbul text-info'
            ];
        }

        return $notifications;
    }

    /**
     * Get marketer notifications
     */
    private function getMarketerNotifications($user)
    {
        $notifications = [];

        // New referrals
        $newReferrals = DB::table('referrals')
            ->where('referrer_id', $user->user_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($newReferrals > 0) {
            $notifications[] = [
                'id' => 'new_referrals_' . $user->user_id . '_' . date('Y-m-d'),
                'type' => 'referral',
                'title' => 'New Referrals',
                'message' => "You have {$newReferrals} new referral(s)",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/marketer/referrals',
                'icon' => 'nc-icon nc-single-02 text-success'
            ];
        }

        // Commission updates
        $pendingCommissions = DB::table('referral_rewards')
            ->where('marketer_id', $user->user_id)
            ->where('status', 'approved')
            ->sum('amount');

        if ($pendingCommissions > 0) {
            $notifications[] = [
                'id' => 'pending_commissions_' . $user->user_id,
                'type' => 'commission',
                'title' => 'Commission Ready',
                'message' => "₦" . number_format($pendingCommissions, 2) . " in commissions ready for payout",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/marketer/payments',
                'icon' => 'nc-icon nc-money-coins text-success'
            ];
        }

        return $notifications;
    }

    /**
     * Get regional manager notifications
     */
    private function getRegionalManagerNotifications($user)
    {
        $notifications = [];

        // Properties pending approval in their region
        $pendingApprovals = Property::where('status', 'pending')
            ->where('state', $user->state) // Assuming regional managers are assigned by state
            ->count();

        if ($pendingApprovals > 0) {
            $notifications[] = [
                'id' => 'pending_approvals_' . $user->user_id,
                'type' => 'approval_required',
                'title' => 'Properties Pending Approval',
                'message' => "{$pendingApprovals} property(ies) in your region require approval",
                'priority' => 'high',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/regional/pending-approvals',
                'icon' => 'nc-icon nc-check-2 text-warning'
            ];
        }

        return $notifications;
    }

    /**
     * Get common notifications for all users
     */
    private function getCommonNotifications($user)
    {
        $notifications = [];

        // Unread messages
        $unreadMessages = Message::where('receiver_id', $user->user_id)
            ->where('is_read', false)
            ->count();

        if ($unreadMessages > 0) {
            $notifications[] = [
                'id' => 'unread_messages_' . $user->user_id,
                'type' => 'message',
                'title' => 'Unread Messages',
                'message' => "You have {$unreadMessages} unread message(s)",
                'priority' => 'medium',
                'timestamp' => now()->toISOString(),
                'read' => false,
                'action_url' => '/dashboard/messages/inbox',
                'icon' => 'nc-icon nc-email-85 text-primary'
            ];
        }

        // System announcements (you can add these to a system_announcements table)
        // For now, we'll add a sample announcement
        $notifications[] = [
            'id' => 'system_announcement_' . date('Y-m-d'),
            'type' => 'announcement',
            'title' => 'Platform Update',
            'message' => 'New features have been added to improve your experience',
            'priority' => 'low',
            'timestamp' => now()->subHours(2)->toISOString(),
            'read' => false,
            'action_url' => '/dashboard',
            'icon' => 'nc-icon nc-bell-55 text-info'
        ];

        return $notifications;
    }

    /**
     * Check system health
     */
    private function checkSystemHealth()
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Check for high error rates
            $recentErrors = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHour())
                ->count();

            if ($recentErrors > 10) {
                return [
                    'healthy' => false,
                    'message' => "High error rate detected: {$recentErrors} failed jobs in the last hour"
                ];
            }

            // Check for storage issues
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

            if ($usagePercent > 90) {
                return [
                    'healthy' => false,
                    'message' => "Low disk space: " . round($usagePercent, 1) . "% used"
                ];
            }

            return ['healthy' => true];

        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'message' => "System health check failed: " . $e->getMessage()
            ];
        }
    }

    /**
     * Get priority value for sorting
     */
    private function getPriorityValue($priority)
    {
        switch ($priority) {
            case 'high': return 3;
            case 'medium': return 2;
            case 'low': return 1;
            default: return 0;
        }
    }

    /**
     * Send push notification (for future implementation)
     */
    public function sendPushNotification($userId, $title, $message, $data = [])
    {
        // This would integrate with push notification services like FCM, Pusher, etc.
        // For now, we'll just store it in the database
        
        try {
            if (!DB::getSchemaBuilder()->hasTable('user_notifications')) {
                return false;
            }

            DB::table('user_notifications')->insert([
                'user_id' => $userId,
                'notification_id' => uniqid('notif_'),
                'type' => $data['type'] ?? 'general',
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send push notification: ' . $e->getMessage());
            return false;
        }
    }
}