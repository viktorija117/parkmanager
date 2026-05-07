<x-mail::message>
# Reservation Cancelled

Hi {{ $reservation->user->name }},

Your parking reservation for **{{ $reservation->date->format('l, F j, Y') }}**
(space **{{ $reservation->parkingSpace->label }}**) has been cancelled by an administrator.

**Reason:** {{ $reservation->cancellation_reason }}

If you have any questions, please contact your administrator.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
