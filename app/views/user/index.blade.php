@extends('layouts.master')

@section('title', 'Kullanıcılar')


@section('content')


<div class="page-header" id="user">
    <h2>Kullanıcılar</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('user.index'), 'method' => 'GET', 'class' => '')) }}
            <div class="input-group">
                {{ Form::text('q', Input::get('q'), array('class' => 'form-control', 'placeholder' => 'aranacak kelimeyi giriniz')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="pull-right">
            <a href="{{ URL::route('user.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</a>
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>E-posta</th>
            <th>Rol</th>
            <th>Durum</th>
            <th class="text-right">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        @if (count($users) > 0)
        @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <?php
                switch ($user->role) {
                    case 'super': $label = 'label-danger';
                        break;
                    case 'admin': $label = 'label-warning';
                        break;
                    case 'moderator': $label = 'label-info';
                        break;
                    default: $label = 'label-default';
                }
                ?>
                <span class="label {{ $label }}">{{ Config::get('settings.user.roles.'.$user->role) }}</span>
            </td>
            <td>{{ $user->isActive() ? '<span class="label label-success">aktif</span>' : '<span class="label label-danger">pasif</span>' }}</td>
            <td class="text-right" class="text-right">
                {{ Form::open(array('role' => 'form', 'route' => array('user.destroy', $user->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                @if( $user->isActive() || is_super_admin())
                <fieldset>
                    @else
                    <fieldset disabled>
                        @endif
                        {{ HTML::linkRoute('user.edit', '', array($user->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
                        {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
                    </fieldset>
                    {{ Form::close() }}
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="4" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
        </tr>
        @endif
    </tbody>
</table>

{{ $users->links() }}
@stop