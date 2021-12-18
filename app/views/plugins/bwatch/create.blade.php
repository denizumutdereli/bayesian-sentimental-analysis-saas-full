@extends('layouts.master')

@section('title', 'Yeni Hesap Bağla')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni BW Hesabı Bağlantısı</h2>
</div>

{{ Form::open(array('role' => 'form', 'route' => array('bwatch.store', null), 'method' => 'POST')) }}
{{ Form::hidden('user_id', Auth::user()->id) }}


    <div class="col-sm-8">
        <div {{ $errors->has('username') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('username', 'BW Kullanıcı Adı') }}
        {{ Form::text('username', Input::old('username'), array('class' => 'form-control', 'placeholder' => 'BW Kullanıcı adını giriniz')) }}
             {{ $errors->has('username') ? $errors->first('username', '<span class="text-danger">:message</span>') : '' }}
        </div>
    </div>
 
    <div class="col-sm-8">
        <div {{ $errors->has('password') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('password', 'BW Kullanıcı Şifresi') }}
        {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'BW Kullanıcı şifrenizi giriniz')) }}
             {{ $errors->has('password') ? $errors->first('password', '<span class="text-danger">:message</span>') : '' }}
        </div>
    </div>
     
 
    <div class="form-group col-sm-8">

        <small class="help-block"><span class="text-warning">Brandwatch bağlantısı sağlandıktan sonra, domainleriniz ve BW projelerinizi eşleştirebilirsiniz.</span></small>

    </div>


<div class="col-md-12">
    <hr/>
    {{ Form::submit('Bağla', array('class' => 'btn btn-info')); }}
    {{ HTML::link('bwatch', 'İptal', array('class' => 'btn btn-default')) }}
    {{ Form::close() }}
</div>
@stop