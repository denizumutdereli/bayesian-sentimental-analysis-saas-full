@extends('layouts.auth')

@section('title')
YNKLabs - App Sentima - Login
@stop

@section('content')
{{ Form::open(array('role' => 'form', 'class' => 'form-signin')) }}
    <h2 class="form-signin-heading">Giriş</h2>


    {{--@if (count($errors))--}}
    {{--<div class="alert alert-danger" role="alert">--}}
        {{--<ul class="list-unstyled">--}}
            {{--@foreach ($errors->all() as $error)--}}
            {{--<li>{{ $error }}</li>--}}
            {{--@endforeach--}}
        {{--</ul>--}}
    {{--</div>--}}
    {{--@endif--}}

    {{ Form::text('email', Input::old('email'), array('class' => 'form-control', 'placeholder' => 'E-posta adresi', 'autofocus')) }}
    {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Şifre')) }}
    <div class="checkbox">
        <label>
            {{ Form::checkbox('remember', 1, Input::old('remember')) }} Beni hatırla
        </label>
    </div>
    {{ Form::button('Giriş', array('class' => 'btn btn-lg btn-primary btn-block', 'type' => 'submit')) }}
{{ Form::close() }}
@stop