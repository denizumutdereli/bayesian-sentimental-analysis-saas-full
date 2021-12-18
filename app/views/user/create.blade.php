@extends('layouts.master')

@section('title', 'Yeni Kullanıcı Ekle')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni Kullanıcı Ekle</h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('user.store'))) }}

    <div class="row">
        <div class="col-md-4">
            <div {{ $errors->has('email') ? 'class="form-group has-error"' : 'class="form-group"' }} >
                {{ Form::label('email', 'Kullanıcı E-posta') }}
                {{ Form::text('email', Input::old('email'), ['class' => 'form-control', 'placeholder' => 'Kullanıcı E-posta Giriniz']) }}
                {{ $errors->has('email') ? $errors->first('email', '<span class="help-block">:message</span>') : '' }}
            </div>
        </div>
        <div class="col-md-4">
            <div {{ $errors->has('password') ? 'class="form-group has-error"' : 'class="form-group"' }} >
                {{ Form::label('password', 'Şifre') }}
                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => 'Şifre Giriniz']) }}
                {{ $errors->has('password') ? $errors->first('password', '<span class="help-block">:message</span>') : '' }}
            </div>
        </div>
        <div class="col-md-4">
            <div {{ $errors->has('password_confirmation') ? 'class="form-group has-error"' : 'class="form-group"' }} >
                {{ Form::label('password_confirmation', 'Şifre Tekrar') }}
                {{ Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Şifre Tekrar Giriniz']) }}
                {{ $errors->has('password_confirmation') ? $errors->first('password_confirmation', '<span class="help-block">:message</span>') : '' }}
            </div>
        </div>
        <div class="col-md-4">
            <div {{ $errors->has('role') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('role', 'Kullanıcı Rolü') }}
            {{ Form::select('role', $roles, null, ['class' => 'form-control', 'placeholder' => 'Kullanıcı Rolü Giriniz']) }}
            {{ $errors->has('role') ? $errors->first('role', '<span class="help-block">:message</span>') : '' }}
            </div>
        </div>
        
        <div class="col-md-4">
            <div {{ $errors->has('account_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('account_id', 'Hesap Bilgisi') }}
            {{ Form::select('account_id', $accounts, null, ['class' => 'form-control', 'placeholder' => 'Bağlı olduğu hesabı seçiniz']) }}
            {{ $errors->has('account_id') ? $errors->first('account_id', '<span class="help-block">:message</span>') : '' }}
            </div>
        </div>
        
        
        
    </div>

@foreach($permissions as $group => $permission)

<div class="form-group">
    <div class="checkbox">
        <label>
            {{ Form::checkbox($group, 1, false, ['class' => 'checkbox-group']) }} <strong>{{ $permission['label'] }}</strong>
        </label>
    </div>

    <ul class="list-inline clearfix">
        @foreach($permission['actions'] as $key => $value)
        <li class="col-md-3">
            <div class="checkbox">
                <label>
                    {{ Form::checkbox('permissions[]', $key, false, ['class' => 'checkbox-permission']) }} <small>{{ $value }}</small>
                </label>
            </div>
        </li>
        @endforeach
    </ul>
</div>
@endforeach

    <a class="btn btn-default btn-xs" onclick="javascript:$('[type=checkbox]').prop('checked', true); return false;">Hepsini seç</a>
    <a class="btn btn-default btn-xs" onclick="javascript:$('[type=checkbox]').prop('checked', false); return false;">Seçimi kaldır</a>

    <hr />

    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
    {{ HTML::link('user', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop

@section('script')
<script>
    (function() {

        $('.checkbox-group').on('change', function() {
            var checkboxGroup = $(this);
            var group = checkboxGroup.closest('.form-group');

            if (checkboxGroup.is(':checked')) {
                group.find('[type=checkbox]').prop('checked', true);
            } else {
                group.find('[type=checkbox]').prop('checked', false);
            }
        });

        $('.checkbox-permission').on('change', function() {
            checkCheckboxGroup();
        });

        function checkCheckboxGroup()
        {
            $('.checkbox-group').each(function( index ) {

                var checkboxGroup = $(this);
                var group = checkboxGroup.closest('.form-group');

                var checkbox = group.find('ul [type=checkbox]');
                var checkboxChecked = group.find('ul [type=checkbox]:checked');

                console.log('cbox: ' + checkbox.length);
                console.log('cboxChecked: ' + checkboxChecked.length);

                if (checkbox.length === checkboxChecked.length) {
                    checkboxGroup.prop('checked', true);
                } else {
                    checkboxGroup.prop('checked', false);
                }

            });
        }

    })();
</script>
@stop