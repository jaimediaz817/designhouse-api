@component('mail::message')
# Hi,

You have been invited to join the team
**{{ $invitation->team->name}}**.
Because you are already registered to the platform, you just 
need to accept or reject the invitation in your 
[Team Management console]({{ $url }})

The body of your message.

@component('mail::button', ['url' => $url])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
