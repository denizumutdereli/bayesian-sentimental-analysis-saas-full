@extends('...layouts.master')

@section('title', 'Akış')


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
          if(badwords.indexOf(text_list[i]) != -1) 
          {
               text_list[i] = '<a href="#ynk" class="text-danger strong" rel="badword" data-original-title="Kelime Ayırma">'+text_list[i]+'</a>'
          }
     }
     
     return text_list.join(' ');
}
</script>

<div class="page-header">
    <h2>
        <span class="title">Akış (Kullanıcı)</span>
        <div class="btn-group pull-right">
            <button class="btn btn-default" type="button" id="btnBack"><i class="fa fa-arrow-circle-left"></i> Geri</button>
            <button class="btn btn-default btn-loading" type="button" id="btnRefresh" data-loading-text="Yükleniyor..."><i class="fa fa-refresh"></i> Yenile</button>
        </div>
    </h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div id="profile"></div>
    </div>
    <div class="col-md-4">
        <div id="formSearchBox">
            {{ Form::open(array('role' => 'form', 'id' => 'formSearch', 'class' => '')) }}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="select">
                            {{ Form::select('select-domain', $domainLists, null, ['class' => 'form-control', 'id' => 'selectDomain']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-6">
                                {{ Form::select('count', array(20 => 20, 40 => 40, 60 => 60, 80 => 80, 100 => 100), 20, array('class' => 'form-control', 'id' => 'selectCount')) }}
                            </div>
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <label>
                                        {{ Form::checkbox('retweet', true, false, array('id' => 'checkRetweet')) }} RT İçermesin
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
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
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-default btn-block btn-loading" type="submit" id="btnSearch" data-loading-text="Yükleniyor..."><i class="fa fa-search"></i> Filtrele</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<hr/>

<div class="row">
    <!-- content tweets -->
    <div class="col-md-8">

        <div class="loading loading-since text-center">
            <div class="loading-icon">
                <i class="fa fa-spin fa-spinner fa-3x"></i>
            <br/>
            <span class="loading-message" data-message="Sistem tarafından yapılan analiz işlemleri biraz zaman alabilir, lütfen bekleyiniz...">Yükleniyor...</span>
            </div>
        </div>

        <div class="timeline-centered">
            <div class="load-new-wrapper">
                <span class="load-new"><span class="tweet-count">0</span> yeni tweet var yüklemek için tıklayınız...!</span>
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
            <span class="loading-message" data-message="Sistem tarafından yapılan analiz işlemleri biraz zaman alabilir, lütfen bekleyiniz...">Yükleniyor...</span>
        </div>
    </div>

    <!-- sidebar filters -->
    <div class="col-md-4">

    </div>
</div>

<!-- BEGIN: Underscore User Profile Template Definition. -->
<script type="text/template" class="template" id="userTemplate">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12 col-sm-3 text-center">
                    <img src="<% var profile_image_url = rc.users.profile_image_url; %><%= profile_image_url.replace("_normal", "") %>" alt="" class="center-block img-circle img-responsive">
                </div><!--/col-->
                <div class="col-xs-12 col-sm-9">
                    <h2><%= rc.users.name %> <small>@<%= rc.users.screen_name %></small></h2>
                    <p><strong>Hakkında: </strong> <%= rc.users.description %> </p>
                    <p><strong>Lokasyon: </strong> <%= rc.users.location %> </p>
                    <p><strong>Dil: </strong> <span style="text-transform: uppercase;"><%= rc.users.lang %></span> </p>
                    <!--<p><strong>Skills: </strong>-->
                        <!--<span class="label label-info tags">html5</span>-->
                        <!--<span class="label label-info tags">css3</span>-->
                        <!--<span class="label label-info tags">jquery</span>-->
                        <!--<span class="label label-info tags">bootstrap3</span>-->
                    <!--</p>-->
                </div><!--/col-->
                <div class="clearfix"></div>
                <div class="col-xs-12 col-sm-3 text-center">
                    <h2><strong> <%= rc.users.statuses_count %> </strong></h2>
                    <p><small>Tweetler</small></p>
                    <!--                            <button type="button" class="btn btn-primary btn-block"><span class="fa fa-gear"></span> Options </button>-->
                </div><!--/col-->
                <div class="col-xs-12 col-sm-3 text-center">
                    <h2><strong> <%= rc.users.followers_count %> </strong></h2>
                    <p><small>Takipçileri</small></p>
                    <!--                            <button class="btn btn-success btn-block"><span class="fa fa-plus-circle"></span> Follow </button>-->
                </div><!--/col-->
                <div class="col-xs-12 col-sm-3 text-center">
                    <h2><strong> <%= rc.users.friends_count %> </strong></h2>
                    <p><small>Takip Ediliyor</small></p>
                    <!--                            <button class="btn btn-info btn-block"><span class="fa fa-user"></span> View Profile </button>-->
                </div><!--/col-->
                <div class="col-xs-12 col-sm-3 text-center">
                    <h2><strong> <%= rc.users.favourites_count %> </strong></h2>
                    <p><small>Favoriler</small></p>
                    <!--                            <button type="button" class="btn btn-primary btn-block"><span class="fa fa-gear"></span> Options </button>-->
                </div><!--/col-->
            </div><!--/row-->
        </div><!--/panel-body-->
    </div><!--/panel-->
</script>
<!-- END: Underscore User Profile Template Definition. -->

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
              if(listItem.stripped_text.indexOf(words[i].replace(/([^<>İıöşçüğÖÇŞĞÜa-zA-Z0-9\s]+)/i, '')) != -1){
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
                        <small><a href="/twitter/userfeeds/<%= listItem.user.screen_name %>" target="_blank"><%= listItem.user.name %></a> tarafından retweetlendi</small>
                    </div>
                </div>
                <a class="pull-left" href="/twitter/userfeeds/<%= listItem.retweeted_status.user.screen_name %>" target="_new" style="margin-right: 5px;">
                    <img class="media-object" src="<%= listItem.retweeted_status.user.profile_image_url %>" alt="..." width="40">
                </a>
                <div class="media-body">
                    <div>
                        <h5 class="media-heading">
                            <a href="/twitter/userfeeds/<%= listItem.retweeted_status.user.screen_name %>" target="_new">
                                <%= listItem.retweeted_status.user.name %>
                                <small>@<%= listItem.retweeted_status.user.screen_name %></small>
                                <small class="pull-right created" data-created="<%= listItem.created_at %>"><%= jQuery.format.prettyDate(listItem.created_at) %></small>
                            </a>
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
                <a class="pull-left" href="/twitter/userfeeds/<%= listItem.user.screen_name %>" target="_new" style="margin-right: 5px;">
                    <img class="media-object" src="<%= listItem.user.profile_image_url %>" alt="..." width="40">
                </a>
                <div class="media-body">
                    <h5 class="media-heading">
                        <a href="/twitter/userfeeds/<%= listItem.user.screen_name %>" target="_blank">
                            <%= listItem.user.name %>
                            <small>@<%= listItem.user.screen_name %></small>
                            <small class="pull-right created" data-created="<%= listItem.created_at %>">
                                <%= jQuery.format.prettyDate(listItem.created_at) %>
                            </small>
                        </a>
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
                    <div class="check">
                        <span class="label label-success">%<span class="positive-percent"><% if ( listItem.check.positive ){ %><%= listItem.check.positive %><% } else { %>0<% } %></span> Olumlu</span>
                        <span class="label label-danger">%<span class="negative-percent"><% if ( listItem.check.negative ){ %><%= listItem.check.negative %><% } else { %>0<% } %></span> Olumsuz</span>
                        <span class="label label-info">%<span class="neutral-percent"><% if ( listItem.check.neutral ){ %><%= listItem.check.neutral %><% } else { %>0<% } %></span> Nötr</span>
                    </div>
                    <div class="btn-group-action pull-right">
                        <button type="button" class="btn btn-success btn-xs btn-positive" <% if ( listItem.state == 1 || listItem.word_list.badwords.length > 0){ %><%= 'disabled' %><% } %>>
                            <span class="fa fa-check-circle"></span> Olumlu
                        </button>
                        <button type="button" class="btn btn-danger btn-xs btn-negative" <% if ( listItem.state == -1 || listItem.word_list.badwords.length > 0){ %><%= 'disabled' %><% } %>>
                            <span class="fa fa-times-circle"></span> Olumsuz
                        </button>
                        <button type="button" class="btn btn-info btn-xs btn-neutral" <% if ( listItem.state == 0 || listItem.word_list.badwords.length > 0 ){ %><%= 'disabled' %><% } %>>
                            <span class="fa fa-minus-circle"></span> Nötr
                        </button>
                    </div>
                    <% } %>
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
@stop

@section('script')
{{ HTML::script('/vendor/slider/js/bootstrap-slider.js') }}
{{ HTML::script('/vendor/datepicker/js/bootstrap-datepicker.js') }}
{{ HTML::script('/assets/js/user.js') }}
@stop

@section('style')
{{ HTML::style('/assets/css/timeline.css') }}
{{ HTML::style('/vendor/slider/css/slider.css') }}
{{ HTML::style('/vendor/datepicker/css/datepicker3.css') }}
@stop

