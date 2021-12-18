@extends('layouts.master')

@section('title', 'Kelime Ekle')


@section('content')
<div class="page-header" id="create">
    <h2>Kelime Ekle <em>"{{ $tag->name }}"</em></h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('tagupload.create', $tag->id), 'method' => 'PUT')) }}

<div {{ $errors->has('tag') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('tag', 'Kelime') }}
    {{ Form::text('tag', Input::old('tag'), array('class' => 'form-control', 'placeholder' => 'Kelime giriniz')) }}
    {{ $errors->has('tag') ? $errors->first('tag', '<span class="help-block">:message</span>') : '' }}
    <input type="hidden" name="tag_id" id="tag_id" value="{{$tag->id}}">
</div>

{{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
{{ HTML::link('tagupload/'.$tag->id, 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop