@extends('layouts.master')

@section('title', 'AI')


@section('content')

<div class="page-header" id="text">
    <h2>Sentimental</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-12">
            {{ Form::open(array('role' => 'form', 'route' => array('sentimental.index'), 'method' => 'GET', 'class' => '')) }}


            <div class="input-group">
                {{ Form::select('domain', $domainLists, $defaultDomain->id, ['class' => 'form-control', 'placeholder' => 'Lütfen Domain seçiniz']) }}
            </div>

            <div class="input-group col-md-12">
                {{ Form::text('q', Input::get('q'), array('class' => 'form-control', 'placeholder' => 'aranacak kelimeyi giriniz')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-filter"></span></button>
                </span>
            </div>



            {{ Form::close() }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="input-group  pull-right">
            {{ Form::open(array('role' => 'form', 'route' => array('sentimental.create'),  'method' => 'POST', 'class' => '')) }}

            <div class="input-group">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">Yeni Ekle</button>
                </span>
            </div>
            {{Form::close()}}

        </div>
    </div>
</div>
<hr/>





<table class="table table-striped">
    <thead>
        <tr>
            <th width="100px">#</th>
            <th width="auto">Metin</th>
            <th width="100px">Kaynak</th>
            <th width="100px">Statü</th>
            <th  width="100px">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        @if (count($sentimentals) > 0)
        @foreach ($sentimentals as $sentimental)
        <tr>
            <td>{{ $sentimental->id }}</td>
            <td style="width:10%">{{ $sentimental->text }}</td>
            <td>
                <?php
                switch ($sentimental->source) {
                    case 'manual': $source = 'Manuel';
                        break;
                    case 'twitter': $source = 'Twitter';
                        break;
                    case 'comment': $source = 'Yorum';
                        break;
                    default: $source = 'Belirsiz';
                }
                ?>
                <span class="label label-default">{{ $source }}</span>
            </td>
            <td>
                <?php
                switch ($sentimental->state) {
                    case -1: $text = 'Olumsuz';
                        $style = 'danger';
                        break;
                    case 0: $text = 'Nötr';
                        $style = 'info';
                        break;
                    case 1: $text = 'Olumlu';
                        $style = 'success';
                        break;
                    default: $text = 'Belirsiz';
                        $style = 'default';
                }
                ?>
                <span class="label label-{{ $style }}">{{ $text }}</span>
            </td>
            <td class="text-right" width="100">
                {{ Form::open(array('role' => 'form', 'route' => array('sentimental.destroy', $sentimental->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                {{ HTML::linkRoute('sentimental.edit', '', array($sentimental->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
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

{{ $sentimentals->appends(array('domain'=>$defaultDomain->id))->links() }}

@stop

@section('style')
<style>
table {
table-layout: fixed;
word-wrap: break-word;
}
</style>
@stop