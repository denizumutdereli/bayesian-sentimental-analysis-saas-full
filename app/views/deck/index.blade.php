@extends('layouts.master')

@section('title', 'Domain seçin')

@section('content')

<div class="row" style="">
{{ Form::open(array('role' => 'form', 'route' => array('comments.index'), 'method' => 'POST', 'id'=>'select-domain')) }}

    <div class="page-header" id="domainPage">
        <h3>Domain seçin</h3>
    </div>

    <div class="well well-lg center-block col-lg-6" style="float: none;margin-top:30px;">


        <div class="input-group">

            {{ Form::select('domain', [''=>'Lütfen bir domain seçin'] + $domains, null, array('id'=>'domain', 'class' => 'form-control')) }}

            <span class="input-group-btn">
                {{ Form::button('Seç', array('type'=>'submit','class' => 'btn btn-default')); }}
                {{ Form::button('Yeni Ekle',  array('class' => 'btn btn-success', 'id'=> 'add-domain')); }}
            </span>
        </div>

        <small class="help-block"><span class="text-warning">Lütfen işleml yapmak istediğiniz Domain'i seçin.</span></small>       
    </div>
{{Form::close()}}
</div>

@stop

@section('script')
{{ HTML::script('/assets/js/deck.js') }}
@stop

