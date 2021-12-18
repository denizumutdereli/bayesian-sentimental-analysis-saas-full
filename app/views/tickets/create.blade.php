@extends('layouts.master')

@section('title', 'Yeni Talep Oluştur')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni Talep Oluştur</h2>
</div>

{{ Form::open(array('role' => 'form', 'route' => array('ticket.store'), 'class' => 'form-ticket')) }}
    <div {{ $errors->has('title') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('title', 'Başlık') }}
        {{ Form::text('title', Input::old('title'), array('class' => 'form-control', 'placeholder' => 'Başlık giriniz')) }}
        {{ $errors->has('title') ? $errors->first('title', '<span class="help-block">:message</span>') : '' }}
    </div>

    <div {{ $errors->has('description') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('description', 'Açıklama') }}
        {{ Form::textarea('description', Input::old('description'), array('class' => 'form-control', 'placeholder' => 'Açıklama yazınız...', 'rows' => 3)) }}
        {{ $errors->has('description') ? $errors->first('description', '<span class="help-block">:message</span>') : '' }}
    </div>

    <hr/>

    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
    {{ HTML::link('ticket', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}

@stop