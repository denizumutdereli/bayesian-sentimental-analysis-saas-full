@extends('layouts.master')

@section('title', $account->name)


@section('content')



<!--<div class="page-header" id="text">-->
<!--    <h2>Hesap Ayrıntıları</h2>-->
<!--</div>-->

<div class="container">
    <h1>Hesap Bilgilerini Güncelle</h1>
    <hr>
    <div class="row">
        <!-- left column -->
        <div class="col-md-3">
            <div class="text-center">
                @if ($account->logo)
                {{ HTML::image('uploads/'.$account->logo, $account->name, array('class' => 'center-block img-responsive')) }}
                @else
                {{ HTML::image('assets/img/no-image-available.png', $account->name, array('class' => 'center-block img-responsive')) }}
                @endif
                <h6>Farklı bir logo yükleyin...</h6>

                {{ Form::open(array('role' => 'form', 'route' => array('account.update', $account->id), 'method' => 'PUT', 'files' => true)) }}
                {{ Form::file('logo', array('class' => 'form-control-static')) }}

                {{ Form::submit('Yükle', array('class' => 'btn btn-default btn-block')) }}
                {{ Form::close() }}
            </div>
        </div>

        <!-- edit form column -->
        <div class="col-md-9 personal-info">
            <h3>Hesap Bilgileri</h3>

            {{ Form::open(array('role' => 'form', 'route' => array('account.update', $account->id), 'method' => 'PUT', 'class' => 'form-horizontal')) }}
            <div class="form-group">
                {{ Form::label('name', 'Hesap Adı', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::text('name', Input::old('name', $account->name), array('class' => 'form-control')) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form::label('name', 'Hakkında', array('class' => 'col-lg-3 control-label')) }}
                <div class="col-lg-9">
                    {{ Form::textarea('about', Input::old('about', $account->about), array('class' => 'form-control', 'rows' => 3)) }}
                </div>
            </div>


            <div class="form-group">
                {{ Form::label('accountType', 'Hesap Tipi', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::select('accountType', Config::get('settings.account.type'),$account->accountType,  array('class' => 'form-control', 'rows' => 3)) }}
                </div>  </div>

            <div class="form-group">
                {{ Form::label('package', 'Paket', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::select('package', Config::get('settings.account.package'),$account->package,  array('class' => 'form-control', 'rows' => 3)) }}
                </div>  </div>

            <div class="form-group">
                {{ Form::label('api', 'Api Bağlantısı', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::checkbox('api', '1', Input::old('api', $account->api)) }}
                </div>  </div>

            <div class="form-group">
                {{ Form::label('is_active', 'Aktif/Pasif', array('class' => 'col-lg-3 control-label')) }}

                <div class="col-lg-9">
                    {{ Form::checkbox('is_active', '1', Input::old('is_active', $account->is_active)) }}
                </div>
            </div> 

            <div class="form-group">
                <div class="col-lg-offset-3 col-md-9">
                    {{ Form::submit('Güncelle', array('class' => 'btn btn-primary')) }}
                    {{ HTML::link('account', 'İptal', array('class' => 'btn btn-default')) }}
                </div>
            </div>
            {{ Form::close() }}

        </div>

        <hr>
<div class="col-sd-12" id="apiContainer">

    <div class="input-group col-md-6">
        {{ Form::label('api_key', 'Api Key') }}
        <div id="msg"></div>
        <div class="input-group">
            {{ Form::text('api_key', $account->api_key, array('class' => 'form-control', 'readonly')) }}
            <span class="input-group-btn">
                <button id="copy" onclick="return false;" class="btn btn-default" data-clipboard-target="#api_key"><span class="fa fa-copy" title="Kopyala"></span></button>
            </span>
        </div>

    </div>

        <div class="input-group col-md-6">
        {{ Form::label('api_secret', 'Api Secret') }}
        <div id="msg"></div>
        <div class="input-group">
            {{ Form::text('api_secret', $account->api_secret, array('class' => 'form-control', 'readonly')) }}
            <span class="input-group-btn">
                <button id="copy" onclick="return false;" class="btn btn-default" data-clipboard-target="#api_secret"><span class="fa fa-copy" title="Kopyala"></span></button>
            </span>
        </div>

    </div>
      
    <small class="help-block">Api anahtarını değiştirdiğinizde, buna bağlı tüm bağlantılarda da aynı anahtarın değiştirilmesi gerekir</small>

</div>
        <hr>
        <b>Son Güncelleme:</b> {{$account->updated_at}} / {{ $account->updated_person }} tarafından yapıldı.
        <br>
        <b>Oluşturma:</b> {{$account->created_at}} / {{ $account->created_person }} tarafından oluşturuldu.


    </div>

    @stop

    @section('script')
    {{ HTML::script('/assets/js/accounts.js') }}
    @stop