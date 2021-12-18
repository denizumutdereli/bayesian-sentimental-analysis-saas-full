@extends('layouts.auth')

@section('title')
YNKLabs - Digitürk - Şifre Değiştir
@stop

@section('content')
{{ Form::open(array('role' => 'form', 'class' => 'form-change')) }}
    <h2 class="form-signin-heading">Şifre Değiştir</h2>

    {{--@if (count($errors))--}}
    {{--<div class="alert alert-danger" role="alert">--}}
        {{--<ul class="list-unstyled">--}}
            {{--@foreach ($errors->all() as $error)--}}
            {{--<li>{{ $error }}</li>--}}
            {{--@endforeach--}}
        {{--</ul>--}}
    {{--</div>--}}
    {{--@endif--}}

    {{-- Form::password('password_old', array('class' => 'form-control', 'placeholder' => 'Eski Şifre')) --}}
    {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Şifre')) }}
    {{ Form::password('password_confirmation', array('class' => 'form-control', 'placeholder' => 'Şifre tekrar')) }}
    {{ Form::button('Değiştir', array('class' => 'btn btn-lg btn-primary btn-block', 'type' => 'submit')) }}

    <small>{{ HTML::link('/', 'Anasayfaya geri dön.', array('class' => '')) }}</small>

{{ Form::close() }}
@stop