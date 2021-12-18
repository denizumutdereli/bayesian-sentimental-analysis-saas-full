@extends('layouts.sidebar')

@section('title', 'Akış Yorumlar')

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
            <div id="published">
                <div class="form-group">
                    <div class="select">
                        {{--{{ Form::label('select-count', 'Yorumlar İçerisinden') }}--}}
                        {{ Form::select('select-published', [0 => 'Yayınlanmış ve Yayınlanmamış Yorumlar', 1 => 'Sadece Yayınlanmış Yorumlar', 2 => 'Sadece Yayınlanmamış Yorumlar'], 0, ['class' => 'form-control', 'id' => 'selectPublished']) }}
                    </div>
                </div>
            </div>
            <div id="processed">
                <div class="form-group">
                    <div class="select">
                        {{--{{ Form::label('select-count', 'Yorumlar İçerisinden') }}--}}
                        {{ Form::select('select-processed', [0 => 'İşlenmiş ve İşlenmemiş Yorumlar', 1 => 'Sadece İşlenmiş Tüm Yorumlar', 2 => 'Sadece İşlenmiş Olumlu Yorumlar', 3 => 'Sadece İşlenmiş Olumsuz Yorumlar', 4 => 'Sadece İşlenmiş Nötr Yorumlar', 5 => 'Sadece İşlenmemiş Yorumlar'], 0, ['class' => 'form-control', 'id' => 'selectProcessed']) }}
                    </div>
                </div>
            </div>
            <div id="order">
                <div class="form-group">
                    <div class="select">
                        {{--{{ Form::label('select-count', 'Sıralama') }}--}}
                        {{--{{ Form::select('select-order', [0 => 'Baştan Sona', 1 => 'Sondan Başa', 2 => 'Karışık'], 0, ['class' => 'form-control', 'id' => 'selectOrder']) }}--}}
                        {{ Form::select('select-order', [0 => 'Baştan Sona', 1 => 'Sondan Başa'], 0, ['class' => 'form-control', 'id' => 'selectOrder']) }}
                    </div>
                </div>
            </div>
            <div id="include">
                <div class="form-group">
                    <div class="select">
                        {{--{{ Form::label('text-include', 'Kelimeleri içersin') }}--}}
                        {{ Form::text('text-search', null, array('class' => 'form-control', 'id' => 'textSearch', 'placeholder' => 'aramak istediğiniz kelimeyi giriniz')) }}
                    </div>
                </div>
            </div>
            <div id="count" class="form-inline">
                <div class="form-group">
                    <div class="select">
                        {{ Form::select('select-count', array(20 => 20, 40 => 40, 60 => 60, 80 => 80, 100 => 100, 200 => 200), 20, array('class' => 'form-control', 'id' => 'selectCount')) }}
                        {{--{{ Form::label('select-count', 'Yorum Göster') }}--}}
                    </div>
                </div>
            </div>

            <div id="inputSlider">

                <hr>
                {{--<h4>Sınırlama Kriteri Belirle</h4>--}}
                <div class="form-group">
                    {{--                                {{ Form::label('input-slider', 'Sınırlama Kriteri Belirle') }}--}}
                    <div class="slider-input text-success">
                        <div class="radio" style="margin-bottom: 0;">
                            <label>
                                {{ Form::radio('slider-check', true, false, array('id' => 'sliderPositiveCheck', 'class' => 'radio-slider-check')) }} <b>Olumlu</b> %(<span class="slider-percent">0</span>)
                            </label>
                        </div>
                        <input type="text" class="input-slider" value="0" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="[0,100]" data-slider-id="sliderPositive" id="sliderPositive" data-slider-tooltip="hide" data-slider-handle="round" >
                    </div>
                    <div class="slider-input text-danger">
                        <div class="radio" style="margin-bottom: 0;">
                            <label>
                                {{ Form::radio('slider-check', true, false, array('id' => 'sliderNegativeCheck', 'class' => 'radio-slider-check')) }} <b>Olumsuz</b> %(<span class="slider-percent">0</span>)
                            </label>
                        </div>
                        <input type="text" class="input-slider" value="0" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="[0,100]" data-slider-id="sliderNegative" id="sliderNegative" data-slider-tooltip="hide" data-slider-handle="round" >
                    </div>
                    <div class="slider-input text-info">
                        <div class="radio" style="margin-bottom: 0;">
                            <label>
                                {{ Form::radio('slider-check', true, false, array('id' => 'sliderNeutralCheck', 'class' => 'radio-slider-check')) }} <b>Nötr</b> %(<span class="slider-percent">0</span>)
                            </label>
                        </div>
                        <input type="text" class="input-slider" value="0" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="[0,100]" data-slider-id="sliderNeutral" id="sliderNeutral" data-slider-tooltip="hide" data-slider-handle="round" >
                        <span class="help-block"><small>Not: Sınırlama kriterlerinden sadece bir tanesini seçebilirsiniz!</small></span>
                    </div>
                </div>
            </div>

            {{--<hr>--}}

            {{--<div id="processed">--}}
            {{--<div class="form-group">--}}
            {{--<div class="checkbox">--}}
            {{--<label>--}}
            {{--<input type="checkbox" class="processed" id="checkProcessed"> Sadece <u>işlenmemiş</u> yorumları göster--}}
            {{--</label>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}

            <hr>


            <div id="tagContainer">

                <div class="input-group col-md-12">
                    {{ Form::label('tagbox','Kelime Kategorileri:')}}
                    <div class="input-group">

                        {{ Form::select('tagsbox', [''=>'Lütfen bir kelime kategorisi seçin'] + $tags, null, array('id'=>'tagsbox', 'class' => 'form-control', 'placeholder' => 'Lütfen kelime grubunu seçin')) }}

                        <span class="input-group-btn">
                            <a href="#" class="btn btn-info" id="add-tag" href=""><span class="fa fa-plus"></span></a>
                            <a class="btn btn-default" href="{{ URL::route('tag.create', array()) }}"><span class="fa fa-chain"></span></a>
                        </span>
                    </div>
                </div>
                <small class="help-block text-warning">Kelime kategorilerini filitreleyebilirsiniz. </small>

            </div>

            <div id="abuse">
                <div class="form-group">
                    <ul class="list-group col-md-12" id="tagslist" style="padding:1px;"></ul>
                </div>
            </div>

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

@include('.inc.learned')

<div class="page-header">
    <h2>Akış (Yorumlar)
        <div class="pull-right"><button class="btn btn-default" onClick ="$('#commentsTable').tableExport({type: 'excel', escape: 'false'});">Excel çıktısı al</button> <button class="btn btn-default" id="menu-toggle">Toggle Filter</button>
        </div>
    </h2>
    {{--<h2>Akış (Yorumlar) <small class="pull-right" style="padding-top: 14px;"><em>(Toplam bulunan sonuç: <strong id="totalResult">1236</strong>, Gösterilen: <strong id="showingResult">20</strong>)</em></small></h2>--}}
</div>

<script>
    function mark_as(text, wordlist)
    {
        if (text == undefined || wordlist == undefined) {
            return text;
        }

        var text_list = text.split(" ");

        for (var i = 0; i < text_list.length; i++)
        {
            $.each(wordlist, function (index, category) {
                $.each(category, function (tagid, tags) {
                    $.each(tags, function (key, tag) {
                        if (tag && text_list[i].replace(/([^İıöşçüğÖÇŞĞÜa-zA-Z0-9]+)/i, '').toLocaleLowerCase() == tag.replace(/([^İıöşçüğÖÇŞĞÜa-zA-Z0-9]+)/i, '').toLocaleLowerCase())
                        {
                            text_list[i] = '<a href="#ynk" class="btn btn-default btn-xs" data-id="' + key + '" data-index="' + index + '" data-indexid="' + tagid + '" data-text="' + tag + '" rel="badword" data-original-title="Kara Liste"><span class="fa fa-tag"></span> ' + text_list[i] + '</a>'
                        }
                    });
                });

            });

            if (text_list[i].match(/href=("|')(.*?)("|')/) == null) {
                text_list[i] = '<a href="#ynk" data-toggle="popover" data-text="' + text_list[i] + '" class="suspect" rel="suspect" data-original-title="Kara Liste">' + text_list[i] + '</a>';
            }

        }

        return text_list.join(' ');
    }
</script>

<div class="row">
    <!-- content comments -->
    <div class="col-md-12">

        <div class="loading loading-since text-center">
            <div class="loading-icon">
                <i class="fa fa-spin fa-spinner fa-3x"></i>
                <br/>
                <span class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</span>
            </div>
        </div>

        <div class="timeline-centered">

            <!-- render comments-->
            <div id="comments"></div>
            <!-- end render comments-->

            <article class="timeline-entry begin">
                <div class="timeline-entry-inner">
                    <a href="javascript:void(0)" style="display: inline-block" class="btn-more tooltip-show" id="btnMore" title="Daha fazla yükle" data-placement="right">
                        <div class="timeline-icon" style="-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg);">
                            <i class="fa fa-plus"></i>
                        </div>
                    </a>
                </div>
            </article>
        </div>

        <div class="loading loading-max text-center">
            <i class="fa fa-spin fa-spinner fa-3x"></i>
            <br/>
            <span class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</span>
        </div>
    </div>
</div>


<table id="commentsTable" class="table table-striped hidden">
    <thead>
        <tr class='danger'>
            <th>#</th>
            <th>Text</th>
            <th>State</th>
            <th>Positive</th>
            <th>Negative</th>
            <th>Neutral</th>
        </tr>
    </thead>
    <tbody id="commentsTableBody">

    </tbody>
</table>
<!-- BEGIN: Underscore Tweet Template Definition. -->
<script type="text/template" class="template" id="commentTemplate">

    <% _.each( rc.listItems.result, function( listItem ) { %>

    <%
    listItem.marked_text = mark_as(listItem.text, listItem.word_list);
    %>

    <article class="timeline-entry" id="<%= listItem.id %>" data-id="<%= listItem.id %>">
    <div class="timeline-entry-inner">
    <% switch (listItem.state) {
    case 1: state_bg = 'bg-success'; state_icon = 'fa-check-circle';
    break;
    case -1: state_bg = 'bg-danger'; state_icon = 'fa-times-circle';
    break;
    default: state_bg = 'bg-info'; state_icon = 'fa-minus-circle';
    } %>
    <div class="timeline-icon <%= state_bg %>">
    <i class="fa fa-icon <%= state_icon %>"></i>
    </div>
    <div class="wallbox timeline-label">
    <!--<a class="pull-left" href="https://twitter.com/<%= listItem.username %>" target="_new" style="margin-right: 5px;">
    </a>-->

    <div class="media-body">
    <h5 class="media-heading"><%= listItem.username  %>
    <small class="pull-right created" data-created="<%= listItem.created_at %>">
    <%= jQuery.format.prettyDate(listItem.created_at) %>
    </small>
    </h5>
    <h4><a href="<%= listItem.post_url%>"><%= listItem.post_title %></a></h4>
    <div class="media-text"><%= listItem.marked_text %></div>
    <% if ( listItem.check ){ %>

    <%
    var roundedCheck = roundFix( [ listItem.check.positive, listItem.check.negative, listItem.check.neutral ], 100 );
    %>
    <div class="check" style="margin-bottom: 10px;margin-top:10px" >
    <span class="label label-success">%<span class="positive-percent" data-sentimental-positive="<%= listItem.check_sentimental.positive%>"><% if ( listItem.check.positive ){ %><%= roundedCheck[0] %><% } else { %>0<% } %></span> <%= rc.names[1] %></span>
    <span class="label label-danger">%<span class="negative-percent" data-sentimental-negative="<%= listItem.check_sentimental.negative%>"><% if ( listItem.check.negative ){ %><%= roundedCheck[1] %><% } else { %>0<% } %></span> <%= rc.names[-1] %></span>
    <span class="label label-info">%<span class="neutral-percent" data-sentimental-neutral="<%= listItem.check_sentimental.neutral%>"><% if ( listItem.check.neutral ){ %><%= roundedCheck[2] %><% } else { %>0<% } %></span> <%= rc.names[0] %></span>
    </div>
    <% } %>
    <div class="btn-group-action">
    <div class="pull-left" id="pos-tagging">
    <% if ( listItem.postagging){ %>
    <%= listItem.postagging %>
    <%} %>
    <?php /*
      <% if ( listItem.is_published == 0 ){ %>
      <button type="button" class="btn btn-default btn-xs btn-publish" <% if(listItem.state == -1 || listItem.word_list.badwords.length > 0){%>disabled<% } %>><span class="fa fa-send"></span> Yayınla</button>
      <% } else { %>
      <button type="button" class="btn btn-success btn-xs btn-publish" <% if(listItem.state == -1 || listItem.word_list.badwords.length > 0){%>disabled<% } %>><span class="fa fa-send"></span> Yayından kaldır</button>
      <%} %>
     *
     */ ?>
    </div>
    <div class="pull-right">
    <button type="button" class="btn btn-success btn-xs btn-positive" <% if ( listItem.state == 1 ){ %><%= 'disabled' %><% } %>><span class="fa fa-check-circle"></span> <%= rc.names[1] %></button>
    <button type="button" class="btn btn-danger btn-xs btn-negative" <% if ( listItem.state == -1 ){ %><%= 'disabled' %><% } %>><span class="fa fa-times-circle"></span> <%= rc.names[-1] %></button>
    <button type="button" class="btn btn-info btn-xs btn-neutral" <% if ( listItem.state == 0 ){ %><%= 'disabled' %><% } %>><span class="fa fa-minus-circle"></span> <%= rc.names[0] %></button>
    </div>
    </div>
    </div>
    </div>
    </div>
    </article>
    <% }); %>

</script>
<!-- END: Underscore Tweet Template Definition. -->

<table id="commentsTable" class="table table-striped hidden">
    <thead>
        <tr class='danger'>
            <th>#</th>
            <th>Text</th>
            <th>State</th>
            <th>Positive</th>
            <th>Negative</th>
            <th>Neutral</th>
        </tr>
    </thead>
    <tbody id="commentsTableBody"></tbody>
</table>

<!-- BEGIN: Underscore Tweet Table Template Definition. -->
<script type="text/template" class="template" id="commentTableTemplate">

    <% _.each( rc.listItems.result, function( listItem ) { %>

    <%
    var roundedCheck = roundFix( [ listItem.check.positive, listItem.check.negative, listItem.check.neutral ], 100 );
    %>

    <tr>
    <td><%= listItem.id %></td>
    <td><%= Base64._utf8_encode(listItem.text) %></td>
    <td><%= listItem.state %></td>
    <td><%= roundedCheck[0] %></td>
    <td><%= roundedCheck[1] %></td>
    <td><%= roundedCheck[2] %></td>
    </tr>

    <% }); %>

</script>
<!-- END: Underscore Tweet Table Template Definition. -->
@stop


@section('script')
{{ HTML::script('/vendor/slider/js/bootstrap-slider.js') }}
{{ HTML::script('/vendor/datepicker/js/bootstrap-datepicker.js') }}
{{ HTML::script('/assets/js/comment.js') }}
@stop

@section('style')
{{ HTML::style('/assets/css/timeline.css') }}
{{ HTML::style('/vendor/slider/css/slider.css') }}
{{ HTML::style('/vendor/datepicker/css/datepicker3.css') }}

<style>
    .text-info {
        color:#0088cc;
        text-decoration: underline;
    }
</style>
@stop
