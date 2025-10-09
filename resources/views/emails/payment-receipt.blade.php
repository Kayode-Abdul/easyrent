@component('mail::message')
# Payment Receipt

Dear {{ $payment->tenant->first_name }},

Thank you for your payment. Your transaction has been successfully processed.

**Transaction Details:**
- Transaction ID: {{ $payment->transaction_id }}
- Amount: {{ $payment->getFormattedAmount() }}
- Property: {{ $payment->apartment->property->address }}
- Apartment: {{ $payment->apartment ? $payment->apartment->apartment_type : 'N/A' }}
- Duration: {{ $payment->duration }} {{ Str::plural('Month', $payment->duration) }}

Your payment receipt is attached to this email.

@component('mail::button', ['url' => route('payments.receipt', $payment->transaction_id)])
Download Receipt
@endcomponent

If you have any questions, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
