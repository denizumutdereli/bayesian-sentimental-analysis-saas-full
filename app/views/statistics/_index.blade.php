@extends('...layouts.sidebar')

@section('title', 'İstatistikler')

@section('sidebar')
<div id="formSearchBox">
    {{ Form::open(array('role' => 'form', 'id' => 'formSearch', 'class' => '')) }}
        <div class="row">
            <div class="col-sm-12">
                <div id="domain">
                    <div class="form-group">
                        <div class="select">
                            {{ Form::label('select-domain', 'Domain Seçiniz') }}
                            {{ Form::select('select-domain', $domainLists, $defaultDomain->id, ['class' => 'form-control', 'id' => 'selectDomain']) }}
                        </div>
                    </div>
                </div>
                {{--<div id="published">--}}
                    {{--<div class="form-group">--}}
                        {{--<div class="select">--}}
                            {{--{{ Form::label('select-count', 'Yorumlar İçerisinden') }}--}}
                            {{--{{ Form::select('select-published', [0 => 'Yayınlanmış ve Yayınlanmamış Yorumlar', 1 => 'Sadece Yayınlanmış Yorumlar', 2 => 'Sadece Yayınlanmamış Yorumlar'], 0, ['class' => 'form-control', 'id' => 'selectPublished']) }}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div id="processed">--}}
                    {{--<div class="form-group">--}}
                        {{--<div class="select">--}}
                            {{--{{ Form::label('select-count', 'Yorumlar İçerisinden') }}--}}
                            {{--{{ Form::select('select-processed', [0 => 'İşlenmiş ve İşlenmemiş Yorumlar', 1 => 'Sadece İşlenmiş Tüm Yorumlar', 2 => 'Sadece İşlenmiş Olumlu Yorumlar', 3 => 'Sadece İşlenmiş Olumsuz Yorumlar', 4 => 'Sadece İşlenmiş Nötr Yorumlar', 5 => 'Sadece İşlenmemiş Yorumlar'], 0, ['class' => 'form-control', 'id' => 'selectProcessed']) }}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div id="include">--}}
                    {{--<div class="form-group">--}}
                        {{--<div class="select">--}}
                            {{--{{ Form::label('text-include', 'Kelimeleri içersin') }}--}}
                            {{--{{ Form::text('text-search', null, array('class' => 'form-control', 'id' => 'textSearch', 'placeholder' => 'aramak istediğiniz kelimeyi giriniz')) }}--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

                {{--<div id="abuse">--}}
                    {{--<div class="form-group">--}}
                        {{--<div class="checkbox">--}}
                            {{--<label>--}}
                              {{--<input type="checkbox" class="abuse" id="checkAbuse"> Küfür <u>içermeyen</u> yorumları göster--}}
                            {{--</label>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

                <hr/>

                <div class="form-group">
                    <button class="btn btn-default btn-block btn-loading" type="submit" id="btnSearch" data-loading-text="Yükleniyor..."><i class="fa fa-search"></i> Filtrele</button>
                </div>

            </div>
        </div>
    {{ Form::close() }}
</div>
@stop


@section('content')

{{--@include('.inc.learned')--}}

<div class="page-header">
    <h2>İstatistikler
        <div class="pull-right">
            {{--<button class="btn btn-default" onClick ="$('#commentsTable').tableExport({type:'excel',escape:'false'});">Excel çıktısı al</button>--}}
            <button class="btn btn-default" id="menu-toggle">Toggle Filter</button>
        </div>
    </h2>
</div>

<div class="row">

    <style>
        .chart-statistics {
            width: 100%;
        }
        .panel-box .panel-body {
            min-height: 100px;
        }
        .count {
            display: none;
        }
    </style>

    <div class="col-md-12">
        <h4>Öğrenme Durumu</h4>
        <div id="chartStatistic" class="chart-statistics" style="height: 250px;"></div>
    </div>

    <div class="col-md-12">
        <h4>Genel İstatistikler</h4>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default panel-box text-center">
            <div class="panel-heading">Toplam Data</div>
            <div class="panel-body">
                <h1 class="count count-total"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default panel-box text-center">
            <div class="panel-heading">Tonlanmış Data</div>
            <div class="panel-body">
                <h1 class="count count-processed"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default panel-box text-center">
            <div class="panel-heading">Tonlanmamış Data</div>
            <div class="panel-body">
                <h1 class="count count-unprocessed"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-default panel-box text-center">
            <div class="panel-heading">Sakıncalı Data</div>
            <div class="panel-body">
                <h1 class="count count-abuse"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>
    {{-- end public statistics --}}

    <div class="col-md-12">
        <h4>Tonlama İstatistikleri</h4>
    </div>

    <div class="col-md-4">
        <div class="panel panel-success panel-box text-center">
            <div class="panel-heading">Olumlu Tonlama</div>
            <div class="panel-body">
                <h1 class="count count-processed-positive"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-danger panel-box text-center">
            <div class="panel-heading">Olumsuz Tonlama</div>
            <div class="panel-body">
                <h1 class="count count-processed-negative"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-info panel-box text-center">
            <div class="panel-heading">Nötr Tonlama</div>
            <div class="panel-body">
                <h1 class="count count-processed-neutral"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>
    {{-- end processed statistics --}}

    <div class="col-md-12">
        <h4>Yayınlanabilir, Yayınlanamaz ve Küfür İstatistikleri <small><em>(Rastgele seçilen 1000 adet data %60-100 arası değerler için incelenmiştir)</em></small></h4>
    </div>

    <div class="col-md-4">
        <div class="panel panel-success panel-box text-center">
            <div class="panel-heading">Yayınlanabilir</div>
            <div class="panel-body">
                <h1 class="count count-publishable-total"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-danger panel-box text-center">
            <div class="panel-heading">Sakıncalı (Yayınlanamaz)</div>
            <div class="panel-body">
                <h1 class="count count-unpublishable-total"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-warning panel-box text-center">
            <div class="panel-heading">Sakıncalı (Küfür)</div>
            <div class="panel-body">
                <h1 class="count count-unpublishable-abuse-total"></h1>
                <div class="loading loading-default text-center">
                    <i class="fa fa-spin fa-spinner"></i>
                    <br/>
                    <small class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</small>
                </div>
            </div>
        </div>
    </div>
    {{-- end publishable and unpublishable statistics --}}

    <div class="col-sm-12">
        <small><em>NOT: İstatistikler her yarım saatte (30 dk) bir güncellenmektedir. Güncelleme sırasında gecikme yaşanabilir.</em></small>
    </div>

</div>

<!-- BEGIN: Underscore Tweet Table Template Definition. -->
<script type="text/template" class="template" id="commentStatisticsTemplate">



</script>
<!-- END: Underscore Tweet Table Template Definition. -->

@stop


@section('script')
{{ HTML::script('/vendor/morris/morris.min.js') }}
{{ HTML::script('/vendor/morris/raphael-min.js') }}
{{ HTML::script('/vendor/slider/js/bootstrap-slider.js') }}
{{ HTML::script('/vendor/datepicker/js/bootstrap-datepicker.js') }}
{{ HTML::script('/assets/js/statistics.js') }}
@stop

@section('style')
{{ HTML::style('/vendor/morris/morris.css') }}
{{ HTML::style('/vendor/slider/css/slider.css') }}
{{ HTML::style('/vendor/datepicker/css/datepicker3.css') }}
@stop
