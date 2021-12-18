@extends('layouts.master')

@section('title', 'Kelime Kategorileri')


@section('content')


<div class="page-header" id="user">
    <h2>Kelime Kategorileri</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('tag.index'), 'method' => 'GET', 'class' => '')) }}
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
            <a href="{{ URL::route('tag.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</a>
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>#</th>
        <th>Hesap Bilgisi</th>
        <th>Kelime Kategorisi</th>
        <th>Toplam Kayıt</th>
        <th>Ekleyen</th>
        <th>Eklenme Tarihi</th>
        <th>Son Güncelleme Tarihi</th>
        <th class="text-right">İşlemler</th>
    </tr>
    </thead>
    <tbody>
    @if (count($tags) > 0)
    @foreach ($tags as $tag)
    <tr>
        
        <td>{{ $tag->id }}</td>
        <td><span class="label label-success">{{ Account::find($tag->account_id)->name }}</span></td>
        <td><span class="label label-info">{{ $tag->name }}</span></td>
        <td><span class="label label-default">{{ count($tag->uploads) .' kelime'   }}</span></td>
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
            <span class="label {{ $label }}">{{ User::find($tag->user_id)->email }}</span>
        </td>
        <td>{{ $tag->created_at }}</td>
        <td>{{ $tag->updated_at }}</td>
        
        <td class="text-right" class="text-right">
            {{ Form::open(array('role' => 'form', 'route' => array('tag.destroy', $tag->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                <fieldset>
                {{ HTML::linkAction('TagUploadController@show', '', array($tag->id), array('class' => 'btn btn-success fa fa-file tooltip-show', 'title' => 'Dosyalar')) }}
                {{ HTML::linkRoute('tag.edit', '', array($tag->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
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

{{ $tags->links() }}
@stop