@component('mail::message')
# Tenancy Overdue Notice

Dear {{ $user->first_name }},

We are contacting you because your tenancy for the apartment **{{ $apartment->apartment_type }}** at **{{
$apartment->property->address }}** has reached its expiration date on **{{ $apartment->range_end->format('M d, Y') }}**.

According to our records, your tenancy is now considered **Overdue**.

@component('mail::panel')
Please take immediate action to renew your tenancy or contact your landlord to discuss your current status.
@endcomponent

@component('mail::button', ['url' => route('billing.index')])
View My Bills
@endcomponent

If you have already made a payment that is not yet reflected, please ignore this message or upload your proof of payment
via the dashboard.

Thanks,<br>
{{ config('app.name') }}
@endcomponent