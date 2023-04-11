<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tron Energy - Кабинет администратора')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicons/apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('favicons/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('favicons/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('favicons/site.webmanifest')}}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href="{{ asset('css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="main">
    <div class="navigation-wrap">
        <x-menu/>
        <button class="mobile close" id="close-menu"></button>
    </div>
    <div class="content-wrap">
        <div class="mobile">
            <button id="menu" class="menu">Меню</button>
        </div>
        @yield('content')
    </div>
</div>
<script src="{{ asset('js/vdn/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('js/staff.js') }}"></script>
@stack('scripts')
<script type="text/javascript">
    @if($errors->any())
    @foreach ($errors->all() as $error)
    show_message('error', "Ошибка", '{{ $error }}');
    @break
    @endforeach
    @endif
    @if (\Session::has('success'))
    show_message("success", "Успех!", "{{ \Session::get('success') }}");
    @endif
    @if (\Session::has('error'))
    show_message("error", "Ошибка!", "{{ \Session::get('error') }}");
    @endif
    @if (\Session::has('stable'))
    show_stable_message("stable", "Успех!", "{{ \Session::get('stable') }}");
    @endif
    document.querySelectorAll('.copy-text').forEach(elem => {
        elem.addEventListener('click', function (_this) {
            copyToClipboard(_this.target.getAttribute('data-txid'))
        });
    });

    const copyToClipboard = str => {
        const el = document.createElement('textarea');
        el.value = str;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    };
</script>
</body>
</html>
