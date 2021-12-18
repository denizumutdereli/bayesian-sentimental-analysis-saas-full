@extends('layouts.master')

@section('title', 'Hesaplar')


@section('content')


<div class="page-header" id="accounts">
    <h2>Hesaplar</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('account.index'), 'method' => 'GET', 'class' => '')) }}
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
       {{ HTML::linkRoute('account.create', 'Yeni Ekle', null, array('class' => 'btn btn-default')) }}
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>#</th>
        <th>Profil</th>
        <th>Hesap İsmi</th>
        <th>Hesap Tipi</th>
        <th>Durum</th>
        <th>Api</th>
        <th>Oluşturulma Tarihi</th>
        <th>Oluşturulan</th>
        <th>Son Güncelleme</th>        
        <th class="text-right">İşlemler</th>
    </tr>
    </thead>
    <tbody>
    @if (count($accounts) > 0)
    @foreach ($accounts as $acc)
    <tr>
        <td>{{ $acc->id }}</td>
        <td class="col-md-1"> 
                @if ($acc->logo)
                {{ HTML::image('uploads/'.$acc->logo, $acc->name, array('class' => 'img-responsive')) }}
                @else
                {{ HTML::image('assets/img/no-image-available.png', $acc->name, array('class' => 'img-responsive')) }}
                @endif
         </td>
         <td><span class="text-info strong">{{$acc->name}}</span></td>
        <td>
            <?php
            switch($acc->accountType){
                case 'pitching': 
                    $label = 'label-warning';
                    $acc->accountType = 'Pitching';
                    break;
                case 'live': 
                    $label = 'label-success';
                    $acc->accountType = 'Live';
                    break;
                default: 
                    $label = 'label-default';
            }
            ?>
            <span class="label {{ $label }}">{{ $acc->accountType }}</span>
        </td>
        <td>{{ ($acc->is_active == 1) ? '<span class="label label-success">aktif</span>' : '<span class="label label-default">pasif</span>' }}</td>
        <td>{{ ($acc->api == 1) ? '<span class="label label-success">açık</span>' : '<span class="label label-default">kapalı</span>' }}</td>
        <td>{{$acc->created_at}}</td>
        <td>{{ @User::find($acc->created_by)->email }}</td>
        <td>{{$acc->updated_at}}</td>
        <td class="text-right" class="text-right">
            {{ Form::open(array('role' => 'form', 'route' => array('account.destroy', $acc->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                
            <fieldset>
                {{ HTML::linkRoute('account.edit', '', array($acc->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
                {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
                </fieldset>
            {{ Form::close() }}
        </td>
    </tr>
    @endforeach
    @else
    <tr>
        <td colspan="10" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
    </tr>
    @endif
    </tbody>
</table>

{{ $accounts->links() }}
@stop