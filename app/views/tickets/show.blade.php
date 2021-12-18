@extends('layouts.master')

@section('title', 'Ticketler')


@section('content')


<div class="page-header" id="text">
    <h2>
        {{ $ticket->title }} <small>#{{ $ticket->id }} <br/>
            @if($ticket->status == 1)
            <small><span class="label label-success">Açık</span>
                @elseif($ticket->status == 0)
                <small><span class="label label-danger">Kapalı</span>
                    @endif
                    <strong>{{ $ticket->user->email }}</strong> tarafından {{ Date::parse($ticket->created_at)->diffForHumans() }} oluşturuldu. {{ count($ticket->replies) }} yorum var</small></small>
            <div class="pull-right">
                <a href="{{ URL::route('ticket.index', array()) }}" class="btn btn-default"><span class="fa fa-list"></span> Tüm Ticketler</a>
                <a href="{{ URL::route('ticket.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</a>
            </div>
    </h2>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="media">
            <div class="media-body">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong>{{ $ticket->user->email }}</strong> <small class="text-muted">{{ Date::parse($ticket->created_at)->diffForHumans() }}</small></div>
                    <div class="panel-body">
                        {{ $ticket->description }}
                    </div>
                </div>
            </div>
        </div>

        <hr/>
    </div>

    @if($ticket->replies)
    <div class="col-md-12">
        <ul class="media-list">
            @foreach($ticket->replies as $reply)
            <li class="media">
                <div class="media-body">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>{{ $reply->user->email }}</strong> <small class="text-muted">{{ Date::parse($reply->created_at)->diffForHumans() }}</small></div>
                        <div class="panel-body">
                            {{ $reply->description }}
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <hr/>
    @endif


    @if($ticket->status == 1)
    <div class="col-md-12">
        <h4>Cevap Yaz</h4>
        {{ Form::open(array('role' => 'form', 'route' => array('ticket.reply', $ticket->id), 'class' => 'form-ticket')) }}

        {{ Form::hidden('parent_id', $ticket->id) }}
        {{ Form::hidden('account_id', $ticket->account_id) }}
        {{ Form::hidden('title', $ticket->title) }}
        <div {{ $errors->has('description') ? 'class="form-group has-error"' : 'class="form-group"' }} >
            {{--{{ Form::label('description', 'Açıklama') }}--}}
            {{ Form::textarea('description', Input::old('description'), array('class' => 'form-control', 'placeholder' => 'Cevap yazınız...', 'rows' => 3)) }}
            {{ $errors->has('description') ? $errors->first('description', '<span class="help-block">:message</span>') : '' }}
        </div>

        <hr/>

        {{ Form::submit('Gönder', array('class' => 'btn btn-info')); }}
        {{ HTML::link('ticket', 'İptal', array('class' => 'btn btn-default')) }}
        {{ Form::close() }}
    </div>
    @endif
</div>
@stop