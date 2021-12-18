@extends('layouts.master')

@section('title', 'Yeni AI Ekle')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni AI Ekle</h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('sentimental.store'))) }}
    <div {{ $errors->has('text') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('text', 'Kelime') }}
        {{ Form::textarea('text', Input::old('text'), array('class' => 'form-control', 'placeholder' => 'Kelime giriniz', 'rows' => 4)) }}
        {{ $errors->has('text') ? $errors->first('text', '<span class="help-block">:message</span>') : '' }}
    </div>
    <div {{ $errors->has('state') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('state', 'Statü') }}
        {{ Form::select('state', array('1' => 'Olumlu', '-1' => 'Olumsuz', '0' => 'Nötr'), Input::old('state', 1), array('class' => 'form-control')) }}
        {{ $errors->has('state') ? $errors->first('state', '<span class="help-block">:message</span>') : '' }}
    </div>
    <div {{ $errors->has('domain_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('domain_id', 'Domain') }}
        {{ Form::select('domain_id', $domainList, Input::old('domain_id', 1), array('class' => 'form-control')) }}
        {{ $errors->has('domain_id') ? $errors->first('domain_id', '<span class="help-block">:message</span>') : '' }}
    </div>
    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
    {{ HTML::link('sentimental', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop