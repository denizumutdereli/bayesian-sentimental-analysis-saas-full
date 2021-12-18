@extends('layouts.master')

@section('title', 'Talepler')


@section('content')

<div class="page-header" id="text">
    <h2>Talepler</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'route' => array('ticket.index'), 'method' => 'GET', 'class' => '')) }}
            <div class="input-group">
                {{ Form::text('q', Input::get('q'), array('class' => 'form-control', 'placeholder' => 'aranacak kelimeyi giriniz')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <div class="col-md-6">
        <div class="pull-right">
            <a href="{{ URL::route('ticket.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</a>
        </div>
    </div>
</div>

<hr/>

<table class="table table-hover">
    <thead>
    <tr>
        <th>Başlık</th>
        <th class="text-right">İşlemler</th>
    </tr>
    </thead>
    <tbody>
    @if (count($tickets) > 0)
        @foreach ($tickets as $ticket)
        <tr>

            <td>
                <div class="pull-left" style="margin-right: 10px;">
                    @if($ticket->status == 1)
                    <i class="fa fa-exclamation-circle text-success"></i>
                    @else
                    <i class="fa fa-check-circle text-danger"></i>
                    @endif
                </div>
                <div class="pull-left">
                <a href="{{ route('ticket.show', array($ticket->id)) }}">{{ $ticket->title }}</a> <span class="badge">{{ $ticket->replies->count() }}</span> <br/>
                <small><em>#{{ $ticket->id }} {{ !($ticket->user) ?: $ticket->user->email }} - {{ ($ticket->status == 0) ? sprintf('%s kapatıldı', Date::parse($ticket->updated_at)->diffForHumans()) : sprintf('%s açıldı', Date::parse($ticket->created_at)->diffForHumans()) }}</em></small>
                </div>
            </td>
            <td class="text-right">
                @if(Auth::user()->role == 'super')
                    {{ Form::open(array('role' => 'form', 'route' => array('ticket.destroy', $ticket->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                        {{ HTML::linkRoute('ticket.show', '', array($ticket->id), array('class' => 'btn btn-default fa fa-eye tooltip-show', 'title' => 'Göster')) }}
                        @if($ticket->status == 1)
                        {{ HTML::linkRoute('ticket.edit', '', array($ticket->id), array('class' => 'btn btn-info fa fa-edit tooltip-show', 'title' => 'Düzenle')) }}
                        {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
                        @endif
                    {{ Form::close() }}
                @else
                    {{ HTML::linkRoute('ticket.show', '', array($ticket->id), array('class' => 'btn btn-default fa fa-eye tooltip-show', 'title' => 'Göster')) }}
                @endif
            </td>
        </tr>
        @endforeach
    @else
    <tr>
        <td colspan="5" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
    </tr>
    @endif
    </tbody>
</table>

{{ $tickets->links() }}
@stop