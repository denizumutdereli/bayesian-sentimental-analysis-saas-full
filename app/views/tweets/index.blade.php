@extends('...layouts.sidebar')

@section('title', 'Akış Twitter')

@section('sidebar')
<div id="formSearchBox">
    {{ Form::open(array('role' => 'form', 'id' => 'formSearch', 'class' => '')) }}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::text('q', '@Digiturk', array('class' => 'form-control input-lg', 'id' => 'textSearch', 'placeholder' => 'kelime giriniz: @Digiturk')) }}
                </div>
                <div class="form-group">
                    <div class="select">
                        {{ Form::select('select-domain', $domainLists, null, ['class' => 'form-control', 'id' => 'selectDomain']) }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <div class="input-group date">
                        {{ Form::text('until', Input::old('until'), array('class' => 'form-control', 'id' => 'textUntil', 'placeholder' => 'tarih giriniz: yyyy-aa-gg')) }}<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::select('result_type', array('recent' => 'En Son', 'popular' => 'Popüler', 'mixed' => 'Karışık'), 'recent', array('class' => 'form-control', 'id' => 'selectResultType')) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::text('from', Input::old('from'), array('class' => 'form-control', 'id' => 'textFrom', 'placeholder' => 'kimden ör: ligTV')) }}
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::text('extract', Input::old('extract'), array('class' => 'form-control', 'id' => 'textExtract', 'placeholder' => 'çıkartmak istediğiniz kelimeyi giriniz: Digiturk')) }}
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-inline">
                    <div class="form-group">
                        {{ Form::select('count', array(20 => 20, 40 => 40, 60 => 60, 80 => 80, 100 => 100), 20, array('class' => 'form-control', 'id' => 'selectCount')) }}
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                {{ Form::checkbox('retweet', true, false, array('id' => 'checkRetweet')) }} RT dahil etme
                            </label>
                        </div>
                    </div>
                </div>

                <hr/>

                <div id="inputSlider">
                    <h4>Sınırlama Kriteri Belirle</h4>
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
                    </div>
                    <span class="help-block"><small>Not: Sınırlama kriterlerinden sadece bir tanesini seçebilirsiniz!</small></span>
                </div>

                {{--<div id="other">--}}
                    {{--<span class="chk-inner">--}}
                      {{--<label class="checkbox-inline"><input type="checkbox" value=":)" name="attd"><span>Olumlu :)</span></label>--}}
                      {{--<label class="checkbox-inline"><input type="checkbox" value=":(" name="attd"><span>Olumsuz :(</span></label>--}}
                      {{--<label class="checkbox-inline"><input type="checkbox" value="?" name="attd"><span>Soru ?</span></label>--}}
                      {{--<label class="checkbox-inline"><input type="checkbox" value="retweets" name="include"><span>Retweetleri dahil et</span></label>--}}
                    {{--</span>--}}
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


@include('inc.learned')

<script>
function mark_as(text, badwords)
{
     if(text == undefined)  {
          return text;
     }

     var text_list = text.split(" ");

     for(i = 0; i < text_list.length; i++)
     {
          if(badwords.indexOf(text_list[i].replace(/([^<>İıöşçüğÖÇŞĞÜa-zA-Z0-9\s]+)/i, '').toLocaleLowerCase()) != -1)
          {
               text_list[i] = '<span class="text-danger strong" rel="badword" data-original-title="Kelime Ayırma">'+text_list[i]+'</span>'
          }
     }

     return text_list.join(' ');
}
</script>

<div class="page-header">
    <h2>Akış (Tweetler) <div class="pull-right"><button class="btn btn-default btn-loading" type="submit" id="btnRefresh" data-loading-text="Yükleniyor..."><i class="fa fa-refresh"></i> Yenile</button> <button class="btn btn-default" onClick ="$('#tweetTable').tableExport({type:'excel',escape:'false'});">Excel çıktısı al</button> <button class="btn btn-default" id="menu-toggle">Toggle Filter</button></div></h2>
</div>

<div class="row">
    <!-- content tweets -->
    <div class="col-md-9">

        <div class="loading loading-since text-center">
            <div class="loading-icon">
                <i class="fa fa-spin fa-spinner fa-3x"></i>
            <br/>
            <span class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</span>
            </div>
        </div>

        <div class="timeline-centered">
            <div class="load-new">
                <span class="load-new-inner"><span class="tweet-count">0</span> yeni tweet var yüklemek için tıklayınız...!</span>
            </div>

            <!-- render tweets-->
            <div id="tweets"></div>
            <!-- end render tweets-->

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

    <!-- sidebar filters -->
    <div class="col-md-3">
        <div class="panel-group" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapse-title">
                            <i class="fa fa-angle-right"></i> Etikete Göre Filtrele
                        </a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div id="tagFilter">
                            <h4>Etikete Göre Filtrele</h4>
                            <div class="form-group" id="tagFilterList">
                                @foreach ($filters as $filter)
                                <div class="radio">
                                    <label>
                                        {{ Form::radio('tag', $filter, false, array('class' => 'radio-tag')) }} {{ $filter }}
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <hr/>

                            <button class="btn btn-default btn-block btn-loading" type="submit" id="btnTag" data-loading-text="Yükleniyor..."><i class="fa fa-search"></i> Etikete Göre Filtrele</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN: Underscore Tweet Template Definition. -->
<script type="text/template" class="template" id="tweetTemplate">

<% if (rc.listItems.length === 0) { %>
    <div class="text-center" style="position: absolute; right: 0; left: 0; padding-top: 10px;">
        <small>Aradığınız kriterlere uygun kayıt bulunamadı!</small>
    </div>
<% } else { %>

    <% _.each( rc.listItems, function( listItem ) { %>

    <article class="timeline-entry" id="<%= listItem.id_str %>" data-id="<%= listItem.id_str %>" <% if( rc.listItemHide === true ) { %>style="display:none"<% } %>>

    <%
         listItem.text = mark_as(listItem.text, listItem.word_list.badwords)
         if(listItem.retweeted_status) {
              listItem.retweeted_status.text = mark_as(listItem.retweeted_status.text, listItem.word_list.badwords);
         }

         words = listItem.text.split(' ');
         for(i=0; i<words.length; i++)
         {
              //console.log(listItem.stripped_text);
              if(listItem.stripped_text.indexOf(words[i].replace(/([^<>İıöşçüğÖÇŞĞÜa-zA-Z0-9\s]+)/i, '').toLowerCase()) != -1){
                    words[i] = '<span data-toggle="popover" class="suspect" rel="suspect" data-original-title="Kelime Ayırma">'+words[i]+'</span>';
              }
         }
         listItem.text = words.join(' ');
         if(listItem.retweeted_status){
              words = listItem.retweeted_status.text.split(' ');
              for(i=0; i<words.length; i++)
              {
                   if(listItem.retweeted_status.stripped_text.indexOf(words[i].replace(/([^<>İıöşçüğÖÇŞĞÜa-zA-Z0-9\s]+)/i, '')) != -1){
                        words[i] = '<span data-toggle="popover" class="suspect" rel="suspect" data-original-title="Kelime Ayırma">'+words[i]+'</span>';
                   }
              }
              listItem.retweeted_status.text = words.join(' ');
         }
     %>
        <div class="timeline-entry-inner">           
            <% switch (listItem.state_on_db) {
                case 1: state_bg = 'bg-success'; state_icon = 'fa-check-circle';
                    break;
                case -1: state_bg = 'bg-danger'; state_icon = 'fa-times-circle';
                    break;
                default: state_bg = 'bg-info'; state_icon = 'fa-minus-circle';
            } %>
            <div class="timeline-icon <%= state_bg %>">
                <i class="fa fa-icon <%= state_icon %>"></i>
            </div>

            <!-- Retweet -->
            <% if (listItem.retweeted_status) { %>

            <div class="wallbox timeline-label"> <!-- wallbox -->
                <div class="reteweet">
                    <div class="reteweet-icon" style="float: left; width: 40px; text-align: right; margin-right: 5px;">
                        <i class="fa fa-retweet"></i>
                    </div>
                    <div class="reteweet-user">
                        <small><a href="https://twitter.com/<%= listItem.user.screen_name %>" target="_blank" data-user-id="<%= listItem.user.id_str %>" data-user="<%= listItem.user.screen_name %>"><%= listItem.user.name %></a> tarafından retweetlendi</small>
                    </div>
                </div>
                <a class="pull-left" href="https://twitter.com/<%= listItem.retweeted_status.user.screen_name %>" target="_blank" style="margin-right: 5px;" data-user="<%= listItem.retweeted_status.user.screen_name %>" data-user-id="<%= listItem.retweeted_status.user.id_str %>">
                    <img class="media-object" src="<%= listItem.retweeted_status.user.profile_image_url %>" alt="..." width="40">
                </a>
                <div class="media-body">
                    <div>
                        <h5 class="media-heading">
                            <a href="https://twitter.com/<%= listItem.retweeted_status.user.screen_name %>" target="_blank" data-user="<%= listItem.retweeted_status.user.screen_name %>" data-user-id="<%= listItem.retweeted_status.user.id_str %>">
                                <%= listItem.retweeted_status.user.name %>
                                <small>@<%= listItem.retweeted_status.user.screen_name %></small>
                            </a>
                            <a href="https://twitter.com/<%= listItem.retweeted_status.user.screen_name %>" target="_blank" class="user-tweets" data-user="<%= listItem.retweeted_status.user.screen_name %>" data-user-id="<%= listItem.retweeted_status.user.id_str %>"><small><i class="fa fa-twitter"></i> <em>Kullanıcı tweetleri</em></small></a>

                            <small class="pull-right created" data-created="<%= listItem.created_at %>"><%= jQuery.format.prettyDate(listItem.created_at) %></small>
                        </h5>
                        <div class="media-text"><%= listItem.retweeted_status.text %></div>

                        <% if ( listItem.retweeted_status.entities.media ){ %>
                        <div class="media-image">
                            <% _.each( listItem.retweeted_status.entities.media, function( media ) { %>
                            <img src="<%= media.media_url %>" alt="" class="img-responsive" style="padding: 10px 0;"/>
                            <% }); %>
                        </div>
                        <% } %>

                        <div class="media-tweet-detail">
                            <% if(listItem.retweeted_status.retweet_count) { %>
                            <small>RETWEET <%= listItem.retweeted_status.retweet_count %></small>
                            <% } %>
                            <% if(listItem.retweeted_status.favorite_count) { %>
                            <small>FAVORİ <%= listItem.retweeted_status.favorite_count %></small>
                            <% } %>
                        </div>

            <% } else { %> <!-- Not retweet -->

            <div class="wallbox timeline-label"> <!-- wallbox -->
                <a class="pull-left" href="https://twitter.com/<%= listItem.user.screen_name %>" target="_new" style="margin-right: 5px;" data-user="<%= listItem.user.screen_name %>" data-user-id="<%= listItem.user.id_str %>">
                    <img class="media-object" src="<%= listItem.user.profile_image_url %>" alt="..." width="40">
                </a>
                <div class="media-body">
                    <h5 class="media-heading">
                        <a href="https://twitter.com/<%= listItem.user.screen_name %>" target="_blank" data-user="<%= listItem.user.screen_name %>" data-user-id="<%= listItem.user.id_str %>">
                            <%= listItem.user.name %>
                            <small>@<%= listItem.user.screen_name %></small>
                        </a>
                        <a href="https://twitter.com/<%= listItem.user.screen_name %>" target="_blank" class="user-tweets" data-user="<%= listItem.user.screen_name %>" data-user-id="<%= listItem.user.id_str %>"><small><i class="fa fa-twitter"></i> <em>Kullanıcı tweetleri</em></small></a>

                        <small class="pull-right created" data-created="<%= listItem.created_at %>"><%= jQuery.format.prettyDate(listItem.created_at) %></small>
                    </h5>
                    <div class="media-text"><%= listItem.text %></div>

                    <% if ( listItem.entities.media ){ %>
                    <div class="media-image">
                        <% _.each( listItem.entities.media, function( media ) { %>
                        <img src="<%= media.media_url %>" alt="" class="img-responsive" style="padding: 10px 0;"/>
                        <% }); %>
                    </div>
                    <% } %>

                    <div class="media-tweet-detail">
                        <% if(listItem.retweet_count) { %>
                        <small>RETWEET <%= listItem.retweet_count %></small>
                        <% } %>
                        <% if(listItem.favorite_count) { %>
                        <small>FAVORİ <%= listItem.favorite_count %></small>
                        <% } %>
                    </div>

            <% } %> <!-- end if retweet -->

                    <% if ( listItem.check ){ %>
                    <div class="check" style="margin-bottom: 10px;">
                    <div class="check">
                        <span class="label label-success">%<span class="positive-percent"><% if ( listItem.check.positive ){ %><%= listItem.check.positive %><% } else { %>0<% } %></span> Olumlu</span>
                        <span class="label label-danger">%<span class="negative-percent"><% if ( listItem.check.negative ){ %><%= listItem.check.negative %><% } else { %>0<% } %></span> Olumsuz</span>
                        <span class="label label-info">%<span class="neutral-percent"><% if ( listItem.check.neutral ){ %><%= listItem.check.neutral %><% } else { %>0<% } %></span> Nötr</span>
                    </div>
                    </div>
                        <% } %>
                        <div class="btn-group-action">
                        <div class="pull-left">
                            <!--<button type="button" class="btn btn-default btn-xs btn-publish" <% if ( listItem.is_published == 1 ){ %><%= 'disabled' %><% } %>><span class="fa fa-send"></span> Yayınla</button>-->
                        </div>
                        <div class="pull-right">
                            <button type="button" class="btn btn-success btn-xs btn-positive" <% if ( listItem.state == 1 ){ %><%= 'disabled' %><% } %>><span class="fa fa-check-circle"></span> Olumlu</button>
                            <button type="button" class="btn btn-danger btn-xs btn-negative" <% if ( listItem.state == -1 ){ %><%= 'disabled' %><% } %>><span class="fa fa-times-circle"></span> Olumsuz</button>
                            <button type="button" class="btn btn-info btn-xs btn-neutral" <% if ( listItem.state == 0 ){ %><%= 'disabled' %><% } %>><span class="fa fa-minus-circle"></span> Nötr</button>
                        </div>
                    </div>
                </div>
                <!--<br/><br/>-->
                <!--<div class="pull-right">-->
                    <!--<a href="#" class="btn btn-link btn-xs"><span class="glyphicon glyphicon-share-alt"></span> Yanıtla</a>-->
                    <!--<a href="#" class="btn btn-link btn-xs"><span class="glyphicon glyphicon-retweet"></span> Retweetle</a>-->
                    <!--<a href="#" class="btn btn-link btn-xs"><span class="glyphicon glyphicon-star"></span> Favorilere Ekle</a>-->
                    <!--<a href="#" class="btn btn-link btn-xs popper" data-toggle="popover" data-placement="bottom"><span></span>... Daha Fazla</a>-->
                    <!--<div class="popper-content hide">-->
                        <!--<a href="">Tweeti yerleştir</a><br>-->
                        <!--<a href="">@Xxxx kişiyi sessize al</a><br>-->
                        <!--<a href="">Engelle veya Bildir</a>-->
                    <!--</div>-->
                <!--</div>-->
                </div> <!-- media-body -->
            </div> <!-- end wallbox -->
        </div> <!-- end timeline-entry-inner -->
    </article>
    <% }); %>

<% } %>

</script>
<!-- END: Underscore Tweet Template Definition. -->

<table id="tweetTable" class="table table-striped hidden">
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
    <tbody id="tweetTableBody">

    </tbody>
</table>

<!-- BEGIN: Underscore Tweet Table Template Definition. -->
<script type="text/template" class="template" id="tweetTableTemplate">

<% _.each( rc.listItems, function( listItem ) { %>


    <!-- Retweet -->
    <% if (listItem.retweeted_status) { %>

        <tr>
            <td><%= listItem.user.id_str %></td>
            <td><%= Base64._utf8_encode(listItem.retweeted_status.text) %></td>
            <td><%= listItem.state %></td>
            <td><%= listItem.check.positive %></td>
            <td><%= listItem.check.negative %></td>
            <td><%= listItem.check.neutral %></td>
        </tr>

    <% } else { %> <!-- Not retweet -->

        <tr>
            <td><%= listItem.user.id_str %></td>
            <td><%= Base64._utf8_encode(listItem.text) %></td>
            <td><%= listItem.state %></td>
            <td><%= listItem.check.positive %></td>
            <td><%= listItem.check.negative %></td>
            <td><%= listItem.check.neutral %></td>
        </tr>

    <% } %>

<% }); %>

</script>
<!-- END: Underscore Tweet Table Template Definition. -->
@stop

@section('script')
{{ HTML::script('/vendor/slider/js/bootstrap-slider.js') }}
{{ HTML::script('/vendor/datepicker/js/bootstrap-datepicker.js') }}
{{ HTML::script('/assets/js/tweet.js') }}
@stop
@section('style')
{{ HTML::style('/assets/css/timeline.css') }}
{{ HTML::style('/vendor/slider/css/slider.css') }}
{{ HTML::style('/vendor/datepicker/css/datepicker3.css') }}
@stop

