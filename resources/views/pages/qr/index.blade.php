@extends('layouts.index')

@push('style')

@endpush

@push('meta')


@endpush


@section('content')

    <!--  компонент тест  -->
    <qr-scanner></qr-scanner>
    <!-- / компонент тест  -->
@endsection


@push('scripts')
    {{-- место для скриптов   --}}
@endpush


