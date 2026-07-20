<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="fav.png">
    <base href="{{ URL('/') }}">

    @stack('meta')

    {{-- libs --}}

    {{-- общие стили  --}}
    @vite(['resources/assets/styles/main.scss'])
    @stack('style')

</head>

<body id="app">

@include('includes.header.index')

<main>
    @yield('content')
</main>


<!-- компонент модального окна (форма)-->
<modal-manager></modal-manager>


<!-- компонент модального окна (ответ) -->
<toast-notification :toasts="toasts"></toast-notification>


{{-- общие скрипты --}}
@vite(['resources/js/app.js'])
@stack('scripts')

</body>

</html>
