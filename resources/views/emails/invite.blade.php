@component('mail::message')

{{ "Пользователь **$name** приглашает вас присоединиться к нему в проекте " . config('app.name') }}

<a href="{{config('app.url') . '/?code=' . $inviteCode}}">Принять приглашение</a>

{{ config('app.name') }}

@endcomponent
