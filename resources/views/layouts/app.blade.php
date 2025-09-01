@extends('laravel-usp-theme::master')

{{-- Blocos do laravel-usp-theme --}}
{{-- Ative ou desative cada bloco --}}

{{-- Target:card-header; class:card-header-sticky --}}
@include('laravel-usp-theme::blocos.sticky')

{{-- Target: button, a; class: btn-spinner, spinner --}}
@include('laravel-usp-theme::blocos.spinner')

{{-- Target: table; class: datatable-simples --}}
@include('laravel-usp-theme::blocos.datatable-simples')

{{-- Fim de blocos do laravel-usp-theme --}}

@section('title')
  @parent
@endsection

@section('styles')
  @parent
  <style>
    /* usado no BS5 */
    .gap-2 {
      gap: .5rem;
    }

  </style>
@endsection

@section('javascripts_bottom')
  @parent
  @vite('resources/js/app.js')

  <script>
    // Seu código .js
  </script>
@endsection
