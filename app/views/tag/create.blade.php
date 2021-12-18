@extends('layouts.master')

@section('title', 'Yeni Kategori Ekle')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni Kategori Ekle</h2>
</div>

{{ Form::open(array('id'=>'tag_form', 'role' => 'form', 'route' => array('tag.store', null), 'method' => 'POST')) }}
{{ Form::hidden('user_id', Auth::user()->id) }}


<div {{ $errors->has('account_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('account_id', 'Hesap Bilgisi') }}
    {{ Form::select('account_id', $accounts, null, ['class' => 'form-control', 'placeholder' => 'Bağlı olduğu hesabı seçiniz']) }}
    {{ $errors->has('account_id') ? $errors->first('account_id', '<span class="help-block">:message</span>') : '' }}
</div>

<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('name', 'Kategori Adı') }}
    {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'placeholder' => 'Kaynak ismi giriniz')) }}
    {{ $errors->has('name') ? $errors->first('name', '<span class="help-block">:message</span>') : '' }}
</div>

<div>
    <small class="help-block"><span class="text-warning">Bu kategoriye ekleyeceğiniz kelimelerin bulunacağı mention'lar otomatik olarak negatif sayılır.</span></small>
</div>

<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('about', 'Hakkında') }}
    {{ Form::textarea('about', Input::old('about', null), array('class' => 'form-control', 'rows' => 3)) }}
    {{ $errors->has('about') ? $errors->first('name', '<span class="help-block">:message</span>') : '' }}
</div>

<div class="form-group">

    <small class="help-block"><span class="text-warning"> Kategori Düzenle bölümünden, bağlantı şekillerini yönetebilirsiniz.</span></small>

</div>

<hr/>

{{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
{{ HTML::link('tag', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}

@stop

@section('script')
{{ HTML::script('/assets/js/tags.js') }}
@stop