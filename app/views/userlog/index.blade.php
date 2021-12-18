@extends('layouts.master')

@section('title', 'Günlükler')


@section('content')


<div class="page-header" id="user">
    <h2>Günlükler</h2>
</div>

<table class="table">
    <thead>
    <tr>
        <th>#</th>
        <th>E-Posta</th>
        <th>İçerik</th>
        <th>Tarih</th>
        <th>eylem</th>
    </tr>
    </thead>
    <tbody>
    @if (count($logs) > 0)
    @foreach ($logs as $log)
    <tr>
        <td>{{ $log->id }}</td>        
        <td>
            @if($log->user)
            {{ HTML::linkRoute('userlogs.userlog',  $log->user->email, array($log->user_id)) }}
            @else
            <small><em>Kullanıcı artık mevcut değil.</em></small>
            @endif
        </td>
        <td>({{$log->source}}) - {{ $log->log->text }}</td>
        <td>{{ date('d-m-Y H:m:s', strtotime($log->created_at)) }}</td>
        <td>{{ $log->log->action }}</td>
    </tr>
    @endforeach
    @else
    <tr>
        <td colspan="4" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
    </tr>
    @endif
    </tbody>
</table>

{{ $logs->links() }}
@stop