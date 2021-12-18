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

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Hesap Bilgisi</th>
            <th>Kaynak Kategorisi</th>
            <th>Ekleyen</th>
            <th>Eklenme Tarihi</th>
            <th>Son Güncelleme Tarihi</th>
            <th class="text-right">İşlemler</th>
        </tr>
    </thead>
    <tbody>

        <tr>

            <td>{{ $source->id }}</td>
            <td><span class="label label-success"> Name</span></td>
            <td><span class="label label-info">x</span></td>
            <td> 
                <span class="label"> email</span>
            </td>
            <td>t1</td>
            <td>t2</td>

            <td class="text-right" class="text-right">

                <fieldset disabled>

                    {{ HTML::linkRoute('source.edit', '', array($source->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
                    {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-danger fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
                </fieldset>
                {{ Form::close() }}
            </td>
        </tr>

        <tr>
            <td colspan="8" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
        </tr>

    </tbody>
</table>



<button id="add-source">Yeni dosya ekle</button>


<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog" tabindex="-1" role="dialog"  aria-hidden="true">
 {{ Form::open(['route' => 'source.add', 'method' => 'put', 'files' => true, 'class' => 'form-horizontal', 'id' => 'rubForm', 'name' =>'myform']) }}
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">CSV Yükleme</h4>
            </div>
            <div class="modal-body" id="modal-body">


                <p>Some text in the modal.</p>
                <input type="file" id="csvfile" />


            </div>
            <div class="modal-footer">

               {{ Form::submit('Yükle', array('class' => 'btn btn-default btn-success')) }}

                <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>


<div class="loading loading-since text-center center-block" style="position: fixed;
     top: 50% !important;
     left: 50%;
     transform: translate(-50%, -50%);display: none;">
    <div class="loading-icon">
        <i class="fa fa-spin fa-spinner fa-3x"></i>
        <br/>
        <span class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</span>
    </div>
</div>
 


<div id="results">**</div>

@stop

@section('style')
<style>
    #progress {
        display: none;
    }
    .progress-striped {
        position: relative;
    }
    .percent {
        position: absolute;
        display: none;
    }
    .percent .percent-count {
        font-weight: 700;
    }
</style>
@stop

@section('script')
{{ HTML::script('/assets/js/source.js') }}
@stop
