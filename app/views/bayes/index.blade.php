@extends('layouts.master')

@section('title', 'Öğret')

@section('content')

<div class="data-learned" style="margin-bottom: 20px;">
    @include('inc.learned')
</div>


<!--<div class="page-header" id="banner">-->
<!--    <h2>Learning</h2>-->
<!--</div>-->
<div class="well well-lg">
    <div class="form-area" style="margin-bottom: 15px">
        {{ Form::textarea('text', '', array('id' => 'text', 'class' => 'form-control', 'placeholder' => 'Analiz etmek istediğiniz kelime veya metni griniz...', 'rows' => 3)) }}
<!--    <span class="help-block">Sakıncalı kelimeleri buraya girerek filtreleyebilirsiniz.</span>-->
    </div>

    <div class="action-button-group">
        <div class="row">
            <div class="col-sm-5 col-md-4 col-lg-3">
                {{ Form::select('select-domain', $domainLists, $defaultDomain->id, ['class' => 'form-control input-lg', 'id' => 'selectDomain']) }}
            </div>
            <div class="col-sm-7 col-md-8 col-lg-9">
                <button id="positive" class="btn btn-success btn-lg"><span class="fa fa-check-circle"></span> <span class="text">Olumlu</span></button>
                <button id="negative" class="btn btn-danger btn-lg"><span class="fa fa-times-circle"></span> <span class="text">Olumsuz</span></button>
                <button id="neutral" class="btn btn-info btn-lg"><span class="fa fa-minus-circle"></span> <span class="text">Nötr</span></button>
                <button id="analysis" class="btn btn-default btn-lg pull-right"><span class="fa fa-bar-chart-o"></span> Analiz</button>
            </div>
        </div>

        {{--<div class="pull-right" style="padding:0 15px 0">--}}
            {{--<label class="checkbox-inline">--}}
                {{--{{ Form::checkbox('method[0]', 'monoGram', false, array('class' => 'ngram')) }} Unigram--}}
            {{--</label>--}}

            {{--<label class="checkbox-inline">--}}
                 {{--{{ Form::checkbox('method[1]', 'bGram', true, array('class' => 'ngram')) }} Bigram--}}
            {{--</label>--}}

            {{--<label class="checkbox-inline">--}}
                 {{--{{ Form::checkbox('method[2]', '3Gram', true, array('class' => 'ngram')) }} Trigram--}}
            {{--</label>--}}

            {{--<label class="checkbox-inline">--}}
                {{--{{ Form::checkbox('method[3]', '4Gram', false, array('class' => 'ngram')) }} Fourgram--}}
            {{--</label>--}}
            {{--<span class="help-block"><small>(Analiz için bir veya daha fazla metod seçebilirsiniz.)</small></span>--}}
        {{--</div>--}}
    </div>
</div>

{{--<div class="alert alert-dismissable alert-info" style="display: none">--}}

{{--</div>--}}

<div id="progress">
    <h3 class="progress-striped text-success"><span id="labelPositive">Pozitif</span> <small class="percent"><em>%<span class="percent-count text-success" id="percentPositive" data-percent="" style="font-size: 24px">0</span></em></small></h3>
    <div class="progress progress-result progress-striped active">
        <div id="progressBarPositive" class="progress-bar progress-bar-success" data-percent=""></div>
    </div>
    <h3 class="progress-striped text-danger"><span id="labelNegative">Negatif</span> <small class="percent"><em>%<span class="percent-count text-danger" id="percentNegative" data-percent="" style="font-size: 24px">0</span></em></small></h3>
    <div class="progress progress-result progress-striped active">
        <div id="progressBarNegative" class="progress-bar progress-bar-danger" data-percent=""></div>
    </div>
    <h3 class="progress-striped text-info"><span id="labelNeutral">Nötr</span> <small class="percent"><em>%<span class="percent-count text-info" id="percentNeutral" data-percent="" style="font-size: 24px">0</span></em></small></h3>
    <div class="progress progress-result progress-striped active">
        <div id="progressBarNeutral" class="progress-bar progress-bar-info" data-percent=""></div>
    </div>
<!--    <h3 class="progress-striped" class="text-warning"><span>Siyasi</span> <small class="percent"><em>%<span class="percent-count text-warning" id="percentPolitic" data-percent="" style="font-size: 24px">0</span></em></small></h3>-->
<!--    <div class="progress progress-striped active">-->
<!--        <div id="progressBarPolitic" class="progress-bar progress-bar-warning" data-percent=""></div>-->
<!--    </div>-->
</div>
@stop

@section('style')
<style>
    #progress {
        display: none;
    }
    .progress-striped {
        position: relative;
    }
    .percent {
        position: absolute;
        display: none;
    }
    .percent .percent-count {
        font-weight: 700;
    }
</style>
@stop

@section('script')
{{ HTML::script('/assets/js/bayes.js') }}
@stop
