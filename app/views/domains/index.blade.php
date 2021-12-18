@extends('layouts.master')

@section('title', 'Domainler')


@section('content')


<div class="page-header" id="text">
    <h2>Domainler</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('domain.index'), 'method' => 'GET', 'class' => '')) }}
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
        <div class="input-group  pull-right">
            {{ Form::open(array('role' => 'form', 'route' => array('domain.create'),  'method' => 'POST', 'class' => '')) }}
            
            <div class="input-group">
             <?php /*   @if (Auth::user()->role == 'super')
            {{ Form::select('account_id', $accounts, null, ['class' => 'form-control', 'placeholder' => 'Bağlı olduğu hesabı seçiniz']) }}
                @endif*/ ?>
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">Yeni Ekle</button>
                </span>
             </div>
           {{Form::close()}}
            
        </div>
    </div>
</div>
<hr/>
<table class="table">
    <thead>
    <tr>
        <th>#</th>
        <th>Domain Adı</th>
        <th>Varsayılan</th>
        <th>Özel</th>
        <th>Api Key</th>
        <th>Öğrenim Oranı</th>
        <th class="text-right">İşlemler</th>
    </tr>
    </thead>
    <tbody>
    @if (count($domains) > 0)
    @foreach ($domains as $domain)
    <tr>
        <td>{{ $domain->id }}</td>
        <td>{{ $domain->name }}</td>
        <td>{{ $domain->isDefault() ? '<span class="label label-success">varsayılan</span>' : '' }}</td>
        <td>{{ $domain->isPrivate() ? '<span class="label label-info">özel</span>' : '<span class="label label-default">genel</span>' }}</td>
        <td><label class="label label-info">{{$domain->api_key}}</label></td>
        <td>{{ calculate_learning_percent($domain->id) }}</td>
        <td class="text-right" width="100">
            {{ Form::open(array('role' => 'form', 'route' => array('domain.destroy', $domain->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete', 'id' => $domain->id)) }}
                {{ HTML::linkRoute('domain.edit', '', array($domain->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
                {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
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

{{ $domains->links() }}
@stop