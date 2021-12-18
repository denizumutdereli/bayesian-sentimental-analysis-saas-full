@extends('layouts.master')

@section('title', 'Domain Düzenle')


@section('content')
<div class="page-header" id="create">
    <h2>Domain Düzenle <small><em>"{{ $domain->name }}"</em></small></h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('domain.update', $domain->id), 'method' => 'PUT', 'class' => 'form-domain')) }}
{{ Form::hidden('user_id', Auth::user()->id) }}

<div {{ $errors->has('account_id') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('account_id', 'Hesap Bilgisi') }}
    {{ Form::select('account_id', $accounts, $domain->account_id, ['class' => 'form-control', 'placeholder' => 'Bağlı olduğu hesabı seçiniz']) }}
    {{ $errors->has('account_id') ? $errors->first('account_id', '<span class="help-block">:message</span>') : '' }}
</div>

<div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
    {{ Form::label('name', 'Domain Adı') }}
    {{ $errors->has('name') ? $errors->first('name', '<span class="text-danger">Bu alan gereklidir.</span>') : '' }}
    {{ Form::text('name', Input::old('name', $domain->name), array('class' => 'form-control', 'placeholder' => 'Kelime giriniz')) }}
</div>

<div class="row">
    <div class="col-sm-8">
        <div class="form-group">
            {{ Form::label('names[1]', 'Pozitif Etiket İsmi', array('class' => '')) }}
            {{ Form::text('names[1]', Input::old('names[1]', $settings["names"][1]), array('class' => 'form-control', 'placeholder' => 'Filtre adı giriniz')) }}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('adjustment[1]', 'Balans Değeri', array('class' => '')) }}
            <div class="input-group spinner">
                {{ Form::text('adjustment[1]', Input::old('adjustment[1]', $settings["adjustment"][1]), array('class' => 'form-control input-spinner input-spinner-positive', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                <div class="input-group-btn-vertical">
                    <button class="btn btn-default btn-spinner"><i class="fa fa-caret-up"></i></button>
                    <button class="btn btn-default btn-spinner"><i class="fa fa-caret-down"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="form-group">
            {{ Form::label('names[-1]', 'Negatif Etiket İsmi', array('class' => '')) }}
            {{ Form::text('names[-1]', Input::old('names[-1]', $settings["names"][-1]), array('class' => 'form-control', 'placeholder' => 'Filtre adı giriniz')) }}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('adjustment[-1]', 'Balans Değeri', array('class' => '')) }}
            <div class="input-group spinner">
                {{ Form::text('adjustment[-1]', Input::old('adjustment[-1]', $settings["adjustment"][-1]), array('class' => 'form-control input-spinner input-spinner-negative', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                <div class="input-group-btn-vertical">
                    <button class="btn btn-default btn-spinner"><i class="fa fa-caret-up"></i></button>
                    <button class="btn btn-default btn-spinner"><i class="fa fa-caret-down"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="form-group">
            {{ Form::label('names[0]', 'Nötr Etiket İsmi', array('class' => '')) }}
            {{ Form::text('names[0]', Input::old('names[0]', $settings["names"][0]), array('class' => 'form-control', 'placeholder' => 'Filtre adı giriniz')) }}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {{ Form::label('adjustment[0]', 'Balans Değeri', array('class' => '')) }}
            <div class="input-group spinner">
                {{ Form::text('adjustment[0]', Input::old('adjustment[0]', $settings["adjustment"][0]), array('class' => 'form-control input-spinner input-spinner-neutral', 'placeholder' => 'Filtre adı giriniz', 'data-min' => 0, 'data-max' => 100, 'readonly')) }}
                <div class="input-group-btn-vertical">
                    <button class="btn btn-default btn-spinner" disabled="disabled"><i class="fa fa-caret-up"></i></button>
                    <button class="btn btn-default btn-spinner" disabled="disabled"><i class="fa fa-caret-down"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="checkbox">
            <label>
                {{ Form::checkbox('balance', 1, Input::old('balance', $settings["balance"])) }} Balans ayarlarını kullan
            </label>
            <small class="help-block"><span class="text-warning">Balans değeri doğrudan analiz sonuçlarına etki etmektedir, bu alanı işaretlerken lütfen değerleri doğru ayarladığınızdan emin olunuz.</span></small>
        </div>

        <hr/>
    </div>




    <hr />


    <div class="col-md-12" id="sourceContainer">
        @if($sources)
        <div class="input-group col-md-6">
            {{ Form::label('sourcebox','Kaynak Kategorileri:')}}
            {{ $errors->has('sources') ? $errors->first('sources', '<span class="text-danger">Bu alan gereklidir.</span>') : '' }}
            <div {{ $errors->has('sources') ? 'class="input-group has-error"' : 'class="input-group"' }} >

                {{ Form::select('sourcesbox', [''=>'Lütfen bir kaynak kategorisi seçin'] + $sources, null, array('id'=>'sourcesbox', 'class' => 'form-control', 'placeholder' => 'Lütfen kaynak kategogisi seçin')) }}

                <span class="input-group-btn">
                    {{ Form::button('Ekle', array('class' => 'btn btn-default', 'id'=> 'add-source')); }} 
                </span>


            </div>
        </div>
        <small class="help-block">Yeni bir kaynak eklemek için önce eklemek istediğiniz kategorileri seçerek "Kaynak Kategorisi Ekle" butonuna tıklayınız. Birden fazla kategori ekleyerek farklı kombinasyonlar oluşturabilirsiniz. <br/> <span class="text-warning">Not: Kural ekledikten sonra "Kaydet" butonuna basmayı unutmayınız.</span></small>
        @endif
    </div>

    <div class="col-sm-8">
        <ul class="list-group col-sm-4" id="sourceslist">        
            <?php $count = 0; ?>
            @foreach($settings["sources"] as $id => $source)
            <?php $count++; ?>
            <li class="list-group-item"><span class="count">{{ $count }}</span>- {{ $sources[$source]}} <button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Kaldır</span></button>                  
                <input type="hidden" value="{{ $source }}" name="sources[]">
            </li>
            @endforeach        
        </ul>      
    </div>
    @if($tags)
    <div class="col-md-12" id="tagContainer">

        <div class="input-group col-md-6">
            {{ Form::label('tagbox','Kelime Kategorileri:')}}
            <div class="input-group">

                {{ Form::select('tagsbox', [''=>'Lütfen bir kelime kategorisi seçin'] + $tags, null, array('id'=>'tagsbox', 'class' => 'form-control', 'placeholder' => 'Lütfen kelime grubunu seçin')) }}

                <span class="input-group-btn">
                    {{ Form::button('Ekle', array('class' => 'btn btn-default', 'id'=> 'add-tag')); }} 
                </span>
            </div>
        </div>
        <small class="help-block">Yeni bir kaynak eklemek için önce eklemek istediğiniz kategorileri seçerek "Kaynak Kategorisi Ekle" butonuna tıklayınız. Birden fazla kategori ekleyerek farklı kombinasyonlar oluşturabilirsiniz. <br/> <span class="text-warning">Not: Kural ekledikten sonra "Kaydet" butonuna basmayı unutmayınız.</span></small>

    </div>

    <div class="col-sm-8">
        <ul class="list-group col-sm-4" id="tagslist">

            <?php
            if ($settings["tags"] != null) {
                $count = 0;
                ?>
                @foreach($settings["tags"] as $id => $tag)
                <?php $count++; ?>
                <li class="list-group-item"><span class="count">{{ $count }}</span>- {{ $tags[$tag] }} <button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Kaldır</span></button>                  
                    <input type="hidden" value="{{ $tag }}" name="tags[]">
                </li>
                @endforeach 

            <?php } //endif   ?>
        </ul>      
    </div>


    <div class="col-sm-12">
        <div class="checkbox">
            <label>
                {{ Form::checkbox('sense', 1, Input::old('sense', $domain->sense)) }} Kelime benzerliklerinde otomatik işaretle.
            </label>
            <small class="help-block">
                <span class="text-warning">Eğer bu özelliği işaretlerseniz, <a href="https://en.wikipedia.org/wiki/Levenshtein_distance" target="_lank">Levenshtein Distance</a> methodu uygulanır ve kelime işaretleme şansı artar. 
                </span></small>
        </div>

        <hr/>
    </div>
    @endif

    <hr />

 <div class="col-sm-8 form-group">

        <div class="input-group col-md-6">
            {{ Form::label('model','Öğrenme Modeli:')}}
            {{ $errors->has('model') ? $errors->first('tags', '<span class="text-danger">Bu alan gereklidir.</span>') : '' }}
            <div {{ $errors->has('model') ? 'class="input-group has-error"' : 'class="input-group"' }} >

                {{ Form::select('model', $models, Input::old('model', $model), array('id'=>'model', 'class' => 'form-control', 'placeholder' => 'Lütfen öğrenme modelini seçin')) }}

            </div>
        </div>
        <small class="help-block">Eğitim modelini seçerek, farklı tiplerde analiz sonuçlarını test edebilirsiniz. <br/> <span class="text-warning">Not: Naive Bayes standard bir analizken, Acoustic ise Türkçe'ye özel olarak tasarlanmış deneysel bir çalışmadır.</span></small>

    </div>


    <div class="col-sm-6" id="rulesCheckboxContainer">
        <div  {{ $errors->has('rules') ? 'class="input-group has-error"' : 'class="input-group"' }} >
            {{ Form::label('rules', 'Analiz Kuralları') }}
            {{ $errors->has('rules') ? $errors->first('rules', '<span class="text-danger">Bu alan gereklidir.</span>') : '' }}


            <ul class="list-group" id="rules">
                <?php $count = 0; ?>
                @foreach($settings["rules"] as $id => $rules)
                <?php $count++; ?>
                <li class="list-group-item"><span class="count">{{ $count }}</span>- {{ implode(" + ", $rules) }} <button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    @foreach($rules as $value => $rule)
                    <input type="hidden" value="{{ $rule }}" name="rules[{{ $id }}][]">
                    @endforeach
                </li>
                @endforeach
            </ul>

            <div class="checkbox">
                <label class="checkbox-inline">
                    {{ Form::checkbox('unigram', 'unigram', Input::old('unigram')) }} Unigram
                </label>
                <label class="checkbox-inline">
                    {{ Form::checkbox('bigram', 'bigram', Input::old('bigram')) }} Bigram
                </label>
                <label class="checkbox-inline">
                    {{ Form::checkbox('trigram', '3gram', Input::old('trigram')) }} Trigram
                </label>
                <label class="checkbox-inline">
                    {{ Form::checkbox('fourgram', '4gram', Input::old('fourgram')) }} Fourgram
                </label>
                <small class="help-block">Yeni bir kural eklemek için önce eklemek istediğiniz kural veya kuralları seçerek "Analiz Kuralı Ekle" butonuna tıklayınız. Birden fazla kural veya kurallar ekleyerek farklı kombinasyonlar oluşturabilirsiniz. <br/> <span class="text-warning">Not: Kural ekledikten sonra "Kaydet" butonuna basmayı unutmayınız.</span></small>
            </div>
            {{ Form::button('Analiz Kuralı Ekle', array('class' => 'btn btn-default btn-sm', 'id'=> 'add-rule')); }}
        </div>
    </div>

    <div class="col-sm-6">
        <label>Diğer Ayarlar</label>
        <div class="checkbox">
            <label>
                {{ Form::checkbox('is_default', true, Input::old('is_default', $domain->is_default)) }} Varsayılan (default) domain
                <small class="help-block">Eğer bu kutucuğu işaretlerseniz sayfaların ilk açılışında bu domain seçili olarak gelecektir. <br/> <span class="text-warning">Not: Varsayılan olarak sadece bir domain seçebilirsiniz.</span></small>
            </label>
        </div>

        @if(is_user_owner($domain->user->id) || is_super_admin())
        <div class="checkbox">
            <label>
                {{ Form::checkbox('is_private', true, Input::old('is_private', $domain->is_private)) }} Özel (private) domain
                <small class="help-block">Eğer bu kutucuğu işaretlerseniz bu domain üzerinde sizden başka hiç bir kullanıcı öğretme ve düzenleme yapamaz.</small>
            </label>
        </div>
        @endif
    </div>
</div>

<hr />
<div class="col-sd-12" id="apiContainer">
  
         <div class="input-group col-md-6">
        {{ Form::label('domain_secret', 'Domain Secret') }}
        <div id="msg"></div>
        <div class="input-group">
            {{ Form::text('domain_secret', $domain->domain_secret, array('class' => 'form-control', 'readonly')) }}
            <span class="input-group-btn">
                <button id="copy" onclick="return false;" class="btn btn-default" data-clipboard-target="#domain_secret"><span class="fa fa-copy" title="Kopyala"></span></button>
            </span>
        </div>

    </div>
    
    
    <small class="help-block">Api anahtarını değiştirdiğinizde, buna bağlı tüm bağlantılarda da aynı anahtarın değiştirilmesi gerekir</small>

</div>


<hr />

{{ Form::submit('Kaydet', array('class' => 'btn btn-info', 'id'=>'save_btn')); }}
{{ HTML::link('domain', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop



@section('script')
{{ HTML::script('/assets/js/domains.js') }}
@stop