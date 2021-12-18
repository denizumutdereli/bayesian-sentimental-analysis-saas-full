@extends('layouts.auth')

@section('title')
YNKLabs - Digitürk - Register
@stop

@section('content')
{{ Form::open(array('role' => 'form', 'class' => 'form-signup')) }}
    <h2 class="form-signin-heading">Kayıt</h2>

    {{--@if (count($errors))--}}
    {{--<div class="alert alert-danger" role="alert">--}}
        {{--<ul class="list-unstyled">--}}
        {{--@foreach ($errors->all() as $error)--}}
            {{--<li>{{ $error }}</li>--}}
        {{--@endforeach--}}
        {{--</ul>--}}
    {{--</div>--}}
    {{--@endif--}}

    {{ Form::text('email', Input::old('email'), array('class' => 'form-control', 'placeholder' => 'E-posta adresi')) }}
    {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Şifre')) }}
    {{ Form::password('password_confirmation', array('class' => 'form-control', 'placeholder' => 'Şifre tekrar')) }}
    {{ Form::button('Kayıt', array('class' => 'btn btn-lg btn-primary btn-block', 'type' => 'submit')) }}

{{ Form::close() }}
@stop