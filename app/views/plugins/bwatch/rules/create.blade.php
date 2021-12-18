@extends('layouts.master')

@section('title', 'Yeni Kural Ekle')


@section('content')
<div class="page-header" id="create">
    <h2>Yeni Kural Ekle</h2>
</div>

{{ Form::open(array('role' => 'form', 'route' => array('bwrules.store', Input::get('id')), 'method' => 'POST', 'id'=>'create')) }}
{{ Form::hidden('bwatch_id', Input::get('id')) }}

<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('name', 'Kural Adı') }}
    {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'placeholder' => 'Kural adını giriniz')) }}
    {{ $errors->has('name') ? $errors->first('name', '<span class="text-danger">:message</span>') : '' }}
</div>

<div {{ $errors->has('datamark') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('datamark', 'Data işareti') }}
    {{ Form::text('datamark', Input::old('datamark'), array('class' => 'form-control', 'placeholder' => 'Güncellenen datalar için bir işaret giriniz')) }}
    {{ $errors->has('datamark') ? $errors->first('datamark', '<span class="text-danger">:message</span>') : '' }}
</div>

<div {{ $errors->has('fromdate') ? 'class="form-group has-error"' : 'class="form-group"' }} >

    {{ Form::label('fromdate', 'Data süresi:') }}
    {{ Form::select('fromdate', ['0'=>'Tüm data','1'=>'Bu günden itibaren'], 0, array('id'=>'fromdate', 'class' => 'form-control', 'placeholder' => 'Lütfen bir değer seçin')) }}
    {{ $errors->has('fromdate') ? $errors->first('fromdate', '<span class="text-danger">:message</span>') : '' }}

    <small class="help-block">Bu bölümden hangi tarihten sonraki dataların dikkate alınacağını belirleyebilirsiniz.</small>
</div>

@if($projects)
{{ Form::hidden('bwtoken', $bwtoken, array('id'=>'bwtoken')) }}

<!--queries-->
@if($queries) 
@foreach($queries as $val => $key)
{{ Form::hidden('q_'.$val, $key, array('id'=>'q_'.$val)) }}
@endforeach
@endif
<!--queriesEND-->

{{ Form::label('project','Proje:')}}
{{ $errors->has('project') ? $errors->first('project', '<span class="text-danger">Bu alan gereklidir.</span>') : '' }}
<div  id="sourceContainer" {{ $errors->has('project') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::select('project', [''=>'Lütfen bir proje seçin'] + $projects, null, array('id'=>'project', 'class' => 'form-control', 'placeholder' => 'Lütfen işlem yapılacak projeyi seçin')) }}
    <small class="help-block">Bu bölümden BW projelerinize ulaşabilirsiniz.<br/> <span class="text-warning">Not: Eğer bir kural eklenir ve daha sonra proje kaldırılırsa, kuralda otomatik olarak kaldırılır.</span></small>
</div>
@endif
<div class="center-block row">

    <div id="queriesContainer"  class="col-md-8" style="display: none">

        <div class="form-group col-sm-5">
            <select name="from[]" id="search" class="form-control" size="6" multiple="multiple">  
            </select>
        </div>

        <div class="form-group col-sm-2">
            <button type="button" id="search_rightAll" rel="move" class="btn btn-block bg-success"><i class="glyphicon glyphicon-forward"></i></button>
            <button type="button" id="search_rightSelected" rel="move" class="btn btn-block bg-success"><i class="glyphicon glyphicon-chevron-right"></i></button>
            <button type="button" id="search_leftSelected" rel="move" class="btn btn-block bg-success"><i class="glyphicon glyphicon-chevron-left"></i></button>
            <button type="button" id="search_leftAll" rel="move" class="btn btn-block bg-success"><i class="glyphicon glyphicon-backward"></i></button>
        </div>

        <div class="form-group col-sm-5">
            <select name="to[]" id="search_to" class="form-control" size="6" multiple="multiple"></select>
        </div>

        <small class="help-block col-md-8">Bu bölümden hangi Query sonuçlarının etkilenmesini istediğinizi seçenebilirsiniz. <br/> <span class="text-warning"><span id="count">0</span> adet Query seçildi.</span></small>
    </div>

    <div id="domainContainer" class="col-md-4" style="display: none">
        <div {{ $errors->has('domain') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('domain', 'Domain:') }}
            {{ Form::select('domain', [''=>'Lütfen bir domain seçin'] + $domains, null, array('id'=>'domain', 'class' => 'form-control', 'placeholder' => 'Lütfen işlem yapılacak domaini seçin')) }}
            {{ $errors->has('domain') ? $errors->first('domain', '<span class="text-danger">:message</span>') : '' }}
        </div>
    </div>

    <div id="whenContainer" class="col-md-4" style="display: none; width: 370px;">
        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('param1', 'Param (1)', array('class' => '')) }}
                <div class="input-group spinner">
                    {{ Form::text('param1', Input::old('param1', '0'), array('class' => 'form-control input-spinner input-spinner-positive', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                    <div class="input-group-btn-vertical">
                        <button class="btn btn-default btn-spinner"><i class="fa fa-caret-up"></i></button>
                        <button class="btn btn-default btn-spinner"><i class="fa fa-caret-down"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('param2', 'Param (-1)', array('class' => '')) }}
                <div class="input-group spinner">
                    {{ Form::text('param2', Input::old('param2', '0'), array('class' => 'form-control input-spinner input-spinner-negative', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                    <div class="input-group-btn-vertical">
                        <button class="btn btn-default btn-spinner"><i class="fa fa-caret-up"></i></button>
                        <button class="btn btn-default btn-spinner"><i class="fa fa-caret-down"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {{ Form::label('param3', 'Param (0)', array('class' => '')) }}
                <div class="input-group spinner">
                    {{ Form::text('param3', Input::old('param3', '100'), array('class' => 'form-control input-spinner input-spinner-neutral', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                    <div class="input-group-btn-vertical">
                        <button class="btn btn-default btn-spinner" disabled="disabled"><i class="fa fa-caret-up"></i></button>
                        <button class="btn btn-default btn-spinner" disabled="disabled"><i class="fa fa-caret-down"></i></button>
                    </div>
                </div>
            </div>
        </div>


        <small class="help-block"><span class="text-warning">Not: A,B ve C için belirlenecek değerlerin en yükseği hangisiyse, şart olarak o değer ve üstü geçerli olur.<br> Eğer en yüksek C/ Nötr ise sonraki en büyük değer geçerli olur. <br> Eğer, A ve B sıfırsa, C/Nötr 100% olan eşitliklere bakılır. </span></small>


    </div>

    <div class="col-md-6" id="actionContainer" style="display: none;">
        <div {{ $errors->has('action') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('action', 'İşlem:') }}
            {{ Form::select('action', [''=>'Lütfen bir işlem seçin'] + $actions, null, array('id'=>'action', 'class' => 'form-control', 'placeholder' => 'Lütfen yapılacak işlemi seçin')) }}
            {{ $errors->has('action') ? $errors->first('action', '<span class="text-danger">:message</span>') : '' }}
        </div>
        <small class="help-block">Bu bölümden yukarıdaki kriterlere uyan datalar için nasıl bir işlem yapmak istediğinizi seçebilirsiniz.</small>
    </div>


    <div class="col-md-6" id="categoriesContainer" style="display: none;">
        <div {{ $errors->has('categories') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            <input type="hidden" id="categorydata" value="">
                {{ Form::label('category', 'Kategoriler:') }}
                {{ Form::select('category', [''=>'Lütfen bir kategori seçin'], null, array('id'=>'category', 'class' => 'form-control', 'placeholder' => 'Lütfen bir kategori seçin')) }}
        </div>
        <small class="help-block">Bu bölümden dataların kategorilerini değiştirebilirsiniz.</small>
    </div>

    <div class="col-md-12" id="subcategoriesContainer" style="display: none;">
        {{ Form::label('subcategories', 'Alt Kategoriler:') }}
        <div {{ $errors->has('subcategories') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        </div>
        <small class="help-block">Bu bölümden dataların hangi alt kategorilere ekleneceğini seçebilirsiniz.</small>
    </div>

    <div class="col-md-6" id="sentimentContainer" style="display: none;">
        <div {{ $errors->has('sentiment') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('sentiment', 'Sentiment:') }}
            {{ Form::select('sentiment', ['1'=>'Olumlu','-1'=>'Olumsuz','0'=>'Notr'], 0, array('id'=>'sentiment', 'class' => 'form-control', 'placeholder' => 'Lütfen bir değer seçin')) }}
            {{ $errors->has('sentiment') ? $errors->first('sentiment', '<span class="text-danger">:message</span>') : '' }}
        </div>
        <small class="help-block">Bu bölümden dataların duygusal analizlerini değiştirebilirsiniz.</small>
    </div>

    <div class="col-md-6" id="deleteContainer" style="display: none;">
        <div {{ $errors->has('delete') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('delete', 'Silme Onayı:') }}

            {{ Form::checkbox('delete', 1, null, ['class' => '','id'=>'delete']) }}  Evet, onaylıyorum.
            <small class="help-block"><span class="text-danger">Yukarıdaki şartlara uyan datalar silinecek, emin misiniz?</span></small>

            {{ $errors->has('delete') ? $errors->first('delete', '<span class="text-danger">:message</span>') : '' }}
        </div>
    </div>

    <div class="col-md-6 form-group" id="tagContainer" style="display: none;">

        <div {{ $errors->has('tags') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{ Form::label('tags', 'Etiketler:') }}
        </div>
        <small class="help-block">Bu bölümden dataların nasıl etiketleneceğini belirtebilirsiniz.<br><span class="text-warning">En fazla 4 adet etiket ekleyebilirsiniz. </span></small>
    </div>

</div>

<div class="col-md-12">
    <hr/>
    {{ Form::submit('Ekle', array('class' => 'btn btn-info', 'id'=>'save')); }}
    {{ HTML::linkRoute('bwatch.edit', 'İptal', array($bwatch->id), array('class' => 'btn btn-default', 'title' => 'İptal')) }}
    {{ Form::close() }}
</div>
@stop



@section('script')
{{ HTML::script('/assets/js/bwrules.js') }}
@stop