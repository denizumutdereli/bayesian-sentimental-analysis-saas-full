@extends('layouts.master')

@section('title', 'Kaynak Kategorisini Güncelle')


@section('content')
<div class="page-header" id="create">
    <h2>Kaynak Kategorisini Güncelle</h2>
</div>

{{ Form::open(array('role' => 'form', 'route' => array('source.update', $source->id), 'method' => 'PUT')) }}
{{ Form::hidden('user_id', Auth::user()->id) }}


<div {{ $errors->has('account_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('account_id', 'Hesap Bilgisi') }}
    {{ Form::select('account_id', $accounts, $source->account_id, ['class' => 'form-control', 'placeholder' => 'Bağlı olduğu hesabı seçiniz']) }}
    {{ $errors->has('account_id') ? $errors->first('account_id', '<span class="help-block">:message</span>') : '' }}
</div>

<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('name', 'Kaynak Adı') }}
    {{ Form::text('name',  Input::old('about', $source->name), array('class' => 'form-control', 'placeholder' => 'Kaynak ismi giriniz')) }}
    {{ $errors->has('name') ? $errors->first('name', '<span class="help-block">:message</span>') : '' }}
</div>


<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('about', 'Hakkında') }}
    {{ Form::textarea('about', Input::old('about', $source->about), array('class' => 'form-control', 'rows' => 3)) }}
    {{ $errors->has('about') ? $errors->first('name', '<span class="help-block">:message</span>') : '' }}
</div>

<hr/>

{{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
{{ HTML::link('source', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}

<div class="form-group">

    <small class="help-block"><span class="text-warning">Kaynak kategorisini ekledikten sonra, Kaynak Düzenle bölümünden, kaynak bağlantı şekillerini yönetebilirsiniz.</span></small>

</div>
@stop
