@extends('layouts.index')

@push('style')

@endpush

@push('meta')
    <title>Главная</title>
    <meta name="description" content="описание главной">
@endpush


@section('content')

    <h1>{{$title}}</h1>


    <items-component></items-component>

    <!--  компонент тест  -->
    <example-component>
        <div class="skeleton-line-simple skeleton-blink"></div>
    </example-component>
    <!-- / компонент тест  -->

@endsection


@push('scripts')
    {{-- место для скриптов   --}}
@endpush

