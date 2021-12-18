@extends('layouts.master')

@section('title', 'Domain Düzenle')


@section('content')
<div class="page-header" id="create">
    <h2>Domain Düzenle <small><em>"{{ $domain->name }}"</em></small></h2>
</div>
{{ Form::open(array('role' => 'form', 'route' => array('domain.update', $domain->id), 'method' => 'PUT', 'class' => 'form-domain')) }}
    <div {{ $errors->has('name') ? 'class="form-group has-error"' : 'class="form-group"' }} >
        {{ Form::label('name', 'Domain Adı') }}
        {{ Form::text('name', Input::old('name', $domain->name), array('class' => 'form-control', 'placeholder' => 'Kelime giriniz')) }}
        {{ $errors->has('name') ? $errors->first('name', '<span class="help-block">:message</span>') : '' }}
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
            </div>
        </div>

        <div class="col-sm-12" id="rulesCheckboxContainer">
            <div class="form-group">
                {{ Form::label('rules', 'Analiz Kuralları') }}

                <ul class="list-group" id="rules">
                <?php $count = 0; ?>
                    @foreach($settings["rules"] as $id => $rules)
                    <?php $count++; ?>
                    <li class="list-group-item"><span class="count">{{ $count }}</span>- {{ implode(" + ", $rules) }} <button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                        @foreach($rules as $value => $rule)
                            <input type="hidden" value="{{ $value }}" name="rules[{{ $id }}][]">
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
                </div>
            </div>
            {{ Form::button('Analiz Kuralı Ekle', array('class' => 'btn btn-default btn-sm', 'id'=> 'add-rule')); }}
        </div>
    </div>

    <hr/>

    <div class="checkbox">
        <label>
            {{ Form::checkbox('is_default', true, Input::old('is_default', $domain->is_default)) }} Geçerli (default) domain
        </label>
    </div>

    <hr/>

    {{ Form::submit('Kaydet', array('class' => 'btn btn-info')); }}
    {{ HTML::link('domain', 'İptal', array('class' => 'btn btn-default')) }}
{{ Form::close() }}
@stop