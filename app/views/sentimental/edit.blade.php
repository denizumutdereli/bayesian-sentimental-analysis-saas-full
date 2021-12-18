@extends('layouts.master')

@section('title', 'AI Düzenle')


@section('content')
<div class="page-header" id="create">
    <h2>AI Düzenle <small><em>"{{ $sentimental->text }}"</em></small></h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('sentimental.update', $sentimental->id), 'method' => 'PUT')) }}
<?php $disabled = $sentimental->source == "manual" ? "" : "disabled" ?>
    {{ Form::hidden('source', $sentimental->source ) }}
    {{ Form::hidden('source_id', $sentimental->source_id ) }}
    <div {{ $errors->has('text') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('text', 'Metin') }}
        {{ Form::textarea('text', Input::old('text', $sentimental->text), array('class' => 'form-control', 'placeholder' => 'Metin Giriniz', 'rows' => 4, $disabled)) }}
        {{ $errors->has('text') ? $errors->first('text', '<span class="help-block">:message</span>') : '' }}
    </div>
    <div {{ $errors->has('state') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('state', 'Statü') }}
        {{ Form::select('state', array('1' => 'Olumlu', '-1' => 'Olumsuz', '0' => 'Nötr'), Input::old('state', $sentimental->state), array('class' => 'form-control')) }}
        {{ $errors->has('state') ? $errors->first('state', '<span class="help-block">:message</span>') : '' }}
    </div>
    <div {{ $errors->has('domain_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('domain_id', 'Domain') }}
        {{ Form::select('domain_id', $domainList, Input::old('domain_id', $sentimental->domain_id), array('class' => 'form-control')) }}
        {{ $errors->has('domain_id') ? $errors->first('domain_id', '<span class="help-block">:message</span>') : '' }}
    </div>
    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')) }}
    {{ HTML::link('sentimental', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop