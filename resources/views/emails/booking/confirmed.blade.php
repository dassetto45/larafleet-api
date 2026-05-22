@component('mail::message')
# Prenotazione confermata

Ciao **{{ $booking->user->name }}**,

La tua prenotazione è stata confermata con successo.

@component('mail::panel')
**Veicolo:** {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }} ({{ $booking->vehicle->plate }})

**Dal:** {{ $booking->start_at->format('d/m/Y H:i') }}

**Al:** {{ $booking->end_at->format('d/m/Y H:i') }}
@endcomponent

@component('mail::button', ['url' => config('app.url')])
Vai a LaraFleet
@endcomponent

Grazie,
{{ config('app.name') }}
@endcomponent
