@component('mail::message')
{{ "В классе $className произошла ошибка:" }}

{{ $error }}

@if($availableTrx)
{{ "Доступно TRX для делегирования: $availableTrx" }}
@endif
@endcomponent
