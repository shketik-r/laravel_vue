@extends('layouts.index')

@push('style')

@endpush

@push('meta')
    <title>Главная</title>
    <meta name="description" content="описание главной">
@endpush


@section('content')

    <h1>Это стандартный Blade-шаблон</h1>
        <!--  компонент тест  -->
    <example-component></example-component>
    <!-- / компонент тест  -->

@endsection


@push('scripts')
{{-- место для скриптов   --}}
@endpush

