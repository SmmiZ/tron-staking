<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tron Energy - Вход в кабинет администратора</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicons/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('favicons/site.webmanifest')}}">
    <link rel="mask-icon" href="{{asset('favicons/safari-pinned-tab.svg')}}" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>
<div class="auth">
    <div class="auth-wrap" style="text-align: center">
        <div class="auth-logo">
            <a href="{{route('home')}}"><img src="{{asset('img/logo.svg')}}" alt="Tron Energy"></a>
        </div>
        <livewire:auth>
    </div>
</div>
<livewire:scripts>
</body>

</html>