@extends('layouts.master')

@section('title', 'Test')

@section('content')
<div class="page-header" id="create">
    <h2>Test</h2>
</div>

{{ Form::open(array('role' => 'form', 'route' => 'google.update', 'method' => 'PUT', 'files' => true, 'id'=>'uploadForm')) }}

<div>
    {{ Form::file('photo', array('class' => 'form-control-static')) }}
</div>

<hr/>

{{ Form::submit('Test', array('class' => 'btn btn-info')); }}
{{ HTML::link('google', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}

@stop
