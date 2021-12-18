@extends('...layouts.master')

@section('title', 'İstatistikler')


@section('content')

<div class="page-header clearfix">
    <div class="row">
        <div class="col-md-6">
            <h2>İstatistikler</h2>
        </div>
        <div class="col-md-6">
            <div id="formSearchBox" class="text-right" style="margin-bottom: 15px;margin-top: 20px;">
                {{ Form::open(array('role' => 'form', 'id' => 'formSearch', 'class' => 'form-inline')) }}
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="select">
                                    {{ Form::label('select-domain', 'Domain Seçiniz', ['class' => 'hidden-xs']) }}
                                    {{ Form::select('select-domain', $domainLists, $defaultDomain->id, ['class' => 'form-control', 'id' => 'selectDomain']) }}
                                </div>
                            </div>

                            <button class="btn btn-default btn-loading" type="submit" id="btnSearch" data-loading-text="Yükleniyor..."><i class="fa fa-search"></i> Filtrele</button>

                        </div>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div class="row">

    <style>
        svg {
          width: 100% !important
        }
        .chart-statistics > svg {
            width: 100% !important;
        }
        .panel-box .panel-body {
            min-height: 100px;
        }
        .count {
            display: none;
        }
    </style>

    <div class="col-md-12">
        <h4>Toplam Öğrenme Durumu</h4>
        <div id="chartStatisticTotal" class="chart-statistics" style="height: 250px;"></div>

        <h4>Günlük Öğrenme Durumu</h4>
        <div id="chartStatisticDaily" class="chart-statistics" style="height: 250px;"></div>
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
        <h4>Yayınlanabilir, Yayınlanamaz ve Küfür İstatistikleri <small><em>(Rastgele seçilen {{ Config::get('settings.analysis.limit', 1000) }} adet data %{{ implode('-', array(60, 100)) }} arası değerler için incelenmiştir)</em></small></h4>
    </div>

    <div class="col-md-3">
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

    <div class="col-md-3">
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

    <div class="col-md-3">
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

    <div class="col-md-3">
        <div class="panel panel-info panel-box text-center">
            <div class="panel-heading">Nötr</div>
            <div class="panel-body">
                <h1 class="count count-neutral-total"></h1>
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
        @if(Config::get('settings.analysis.cache_time'))
        <small><em>NOT: İstatistikler her yarım saatte ({{ Config::get('settings.analysis.cache_time') }} dk) bir güncellenmektedir. Güncelleme sırasında gecikme yaşanabilir.</em></small>
        @endif
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
