@extends('layouts.master')

@section('title', 'Kaynaklar')


@section('content')


<div class="page-header" id="user">
    <h2>Kaynaklar</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('source.index'), 'method' => 'GET', 'class' => '')) }}
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
            <a href="{{ URL::route('source.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</a>
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>#</th>
        <th>Hesap Bilgisi</th>
        <th>Kaynak Kategorisi</th>
        <th>Toplam Dosya/Kayıt</th>
        <th>Ekleyen</th>
        <th>Eklenme Tarihi</th>
        <th>Son Güncelleme Tarihi</th>
        <th class="text-right">İşlemler</th>
    </tr>
    </thead>
    <tbody>
    @if (count($sources) > 0)
    @foreach ($sources as $source)
    <tr>
        
        <td>{{ $source->id }}</td>
        <td><span class="label label-success">{{ Account::find($source->account_id)->name }}</span></td>
        <td><span class="label label-info">{{ $source->name }}</span></td>
        <td><span class="label label-default">{{ count($source->uploads) .' Dosya / '. $source->uploads()->sum('count') . ' kayıt'   }}</span></td>
         <td>
            <?php
            switch($user->role){
                case 'super': $label = 'label-danger';
                    break;
                case 'admin': $label = 'label-warning';
                    break;
                case 'moderator': $label = 'label-info';
                    break;
                default: $label = 'label-default';
            }
            ?>
            <span class="label {{ $label }}">{{ User::find($source->user_id)->email }}</span>
        </td>
        <td>{{ $source->created_at }}</td>
        <td>{{ $source->updated_at }}</td>
        
        <td class="text-right" class="text-right">
            {{ Form::open(array('role' => 'form', 'route' => array('source.destroy', $source->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                <fieldset>
                {{ HTML::linkRoute('upload.edit', '', array($source->id), array('class' => 'btn btn-success fa fa-file tooltip-show', 'title' => 'Dosyalar')) }}
                {{ HTML::linkRoute('source.edit', '', array($source->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
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

{{ $sources->links() }}
@stop