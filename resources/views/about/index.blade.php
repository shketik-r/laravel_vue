@extends('layouts.index')

@push('style')
    @vite(['resources/scss/page/about.scss'])
@endpush

@push('meta')
    <title>О нас</title>
    <meta name="description" content="описание сраницы">
@endpush


@section('content')

    <h1>Страница о нас</h1>


    <!--  компонент тест  -->
    <example-component></example-component>
    <!-- / компонент тест  -->



@endsection


@push('scripts')
    {{-- место для скриптов   --}}
@endpush


