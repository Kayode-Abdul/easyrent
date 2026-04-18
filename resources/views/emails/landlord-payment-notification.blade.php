@component('mail::message')
# Payment Received

Dear {{ $payment->landlord->first_name }},

Great news! You have received a rent payment from **{{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}**.

## Payment Details

**Property Information:**
- Property: {{ $payment->apartment->property->address ?? 'N/A' }}
- Apartment: {{ $payment->apartment ? $payment->apartment->apartment_type : 'N/A' }}
- Duration: {{ $payment->duration }} {{ Str::plural('Month', $payment->duration) }}

**Financial Breakdown:**
- Gross Amount: {{ format_money($payment->amount, $payment->currency) }}
- Platform Fee (2.5%): {{ format_money($commissionAmount, $payment->currency) }}
- **Net Amount to You: {{ format_money($netAmount, $payment->currency) }}**

**Transaction Details:**
- Transaction ID: {{ $payment->transaction_id }}
- Payment Date: {{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : $payment->created_at->format('M d, Y h:i A') }}
- Payment Method: {{ ucfirst($payment->payment_method ?? 'Card') }}
- Tenant: {{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}

The payment receipt is attached to this email for your records.

@component('mail::button', ['url' => route('dashboard')])
View Dashboard
@endcomponent

@component('mail::panel')
**Rental Period:** {{ $payment->apartment->range_start ? $payment->apartment->range_start->format('M d, Y') : 'N/A' }} to {{ $payment->apartment->range_end ? $payment->apartment->range_end->format('M d, Y') : 'N/A' }}
@endcomponent

If you have any questions about this payment, please contact our support team.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
