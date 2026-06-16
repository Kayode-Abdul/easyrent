@component('mail::message')
# New Message from {{ $sender->first_name }}

You have received a secure message via **EasyRent** from **{{ $sender->first_name }} {{ $sender->last_name }}**.

@component('mail::panel')
**Subject:** {{ $messageData->subject }}

"{{ Str::limit($messageData->body, 150) }}"
@endcomponent

To reply to this message and view the full details, please sign up or log in to your EasyRent account.

@component('mail::button', ['url' => route('register')])
Join EasyRent to Reply
@endcomponent

Thanks,<br>
The {{ config('app.name') }} Team
@endcomponent
