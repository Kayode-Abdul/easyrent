<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';
    protected $description = 'Send payment reminders to tenants with upcoming or overdue payments';

    public function handle()
    {
        // Get tenants with upcoming payments (3 days before due date)
        $upcomingPayments = Payment::whereDate('due_date', '=', Carbon::now()->addDays(3))
            ->where('status', 'pending')
            ->get();

        foreach ($upcomingPayments as $payment) {
            $payment->tenant->notify(new PaymentReminder($payment, 'upcoming'));
        }

        // Get overdue payments
        $overduePayments = Payment::whereDate('due_date', '<', Carbon::now())
            ->where('status', 'pending')
            ->get();

        foreach ($overduePayments as $payment) {
            $payment->tenant->notify(new PaymentReminder($payment, 'overdue'));
        }

        $this->info('Payment reminders sent successfully.');
    }
}
