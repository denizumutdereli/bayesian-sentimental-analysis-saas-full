@extends('layouts.master')

@section('title', 'Talep Düzenle')


@section('content')
<div class="page-header" id="create">
    <h2>Talep Düzenle <small><em>"{{ $ticket->title }}"</em></small></h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('ticket.update', $ticket->id), 'method' => 'PUT', 'class' => 'form-ticket')) }}
    <div {{ $errors->has('title') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('title', 'Başlık') }}
        {{ Form::text('title', Input::old('title', $ticket->title), array('class' => 'form-control', 'placeholder' => 'Başlık giriniz')) }}
        {{ $errors->has('title') ? $errors->first('title', '<span class="help-block">:message</span>') : '' }}
    </div>

    <div {{ $errors->has('description') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('description', 'Açıklama') }}
        {{ Form::textarea('description', Input::old('description', $ticket->description), array('class' => 'form-control', 'placeholder' => 'Açıklama yazınız...', 'rows' => 3)) }}
        {{ $errors->has('description') ? $errors->first('description', '<span class="help-block">:message</span>') : '' }}
    </div>

    <div {{ $errors->has('status') ? 'class="form-group has-error"' : 'class="form-group form-inline"' }} >
        {{--{{ Form::label('status', 'Durum') }}--}}
        {{ Form::select('status', Config::get('settings.ticket.statuses'), Input::old('status', $ticket->status), array('class' => 'form-control')) }}
        {{ $errors->has('status') ? $errors->first('status', '<span class="help-block">:message</span>') : '' }}
    </div>

    <hr/>

    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
    {{ HTML::link('ticket', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop