@extends('layouts.master')

@section('title', 'Yeni Hesap Ekle')


@section('content')



<!--<div class="page-header" id="text">-->
<!--    <h2>Hesap Ayrıntıları</h2>-->
<!--</div>-->

<div class="container">
    <h1>Yeni Hesap Ekle</h1>
    <hr>
    <div class="row">
        <!-- left column -->
        <div class="col-md-3">
            <div class="text-center">
               {{ HTML::image('assets/img/no-image-available.png', null, array('class' => 'center-block img-responsive')) }}
              <h6>Farklı bir logo yükleyin...</h6>

                {{ Form::open(array('role' => 'form', 'route' => array('account.create', null), 'method' => 'PUT', 'files' => true)) }}
                {{ Form::file('logo', array('class' => 'form-control-static')) }}

                {{ Form::submit('Yükle', array('class' => 'btn btn-default btn-block')) }}
                {{ Form::close() }}
            </div>
        </div>

        <!-- edit form column -->
        <div class="col-md-9 personal-info">
            <h3>Hesap Bilgileri</h3>
          
              {{ Form::open(array('role' => 'form', 'action' => 'account.store', 'method' => 'POST', 'class' => 'form-horizontal','files'=>true)) }}
              
              
            <div class="form-group">
                {{ Form::label('name', 'Hesap Adı', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('name', Input::old('name', null), array('class' => 'form-control')) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('name', 'Hakkında', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::textarea('about', Input::old('about', null), array('class' => 'form-control', 'rows' => 3)) }}
                </div>
            </div>


            <div class="form-group">
                {{ Form::label('accountType', 'Hesap Tipi', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::select('accountType', Config::get('settings.account.type'),null,  array('class' => 'form-control', 'rows' => 3)) }}
                </div>  </div>
              
              <div class="form-group">
                {{ Form::label('package', 'Paket', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::select('package', Config::get('settings.account.package'),null,  array('class' => 'form-control', 'rows' => 3)) }}
                </div>  </div>
         
            <div class="form-group">
                {{ Form::label('api', 'Api Bağlantısı', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                   {{ Form::checkbox('api', '1', Input::old('api', null)) }}
                </div>
            </div>
            
           <div class="form-group">
                {{ Form::label('is_active', 'Aktif/Pasif', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                   {{ Form::checkbox('is_active', '1', Input::old('is_active', null)) }}
                </div>
            </div>
 
              
            <div class="form-group">
                <div class="col-lg-offset-3 col-md-9">
                    {{ Form::submit('ekle', array('class' => 'btn btn-primary')) }}
                    {{ HTML::link('account', 'İptal', array('class' => 'btn btn-default')) }}
                </div>
            </div>
            {{ Form::close() }}
        </div>
        
      <hr>
         
        
        
</div>


@stop

@section('script')
{{ HTML::script('/assets/js/accounts.js') }}
@stop