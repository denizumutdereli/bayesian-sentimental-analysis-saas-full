@extends('layouts.master')

@section('title', 'Brandwatch')


@section('content')


<div class="page-header" id="user">
    <h2>Brandwatch Hesap Ayarları</h2>
    <p class="help-block right"><a href="http://www.brandwatch.com/" target="_blank">Brandwatch</a> bölümüne hoş geldiniz. 
        Brandwatch projelerinizi codexAI API üzerinden bağlayabilir ve birlikte uyum içinde çalışmasını sağlayabilirsiniz.</p>
</div>




<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('bwatch' => 'form', 'route' => array('bwatch.index'), 'method' => 'POST', 'class' => '')) }}
            <div class="input-group">
                {{ Form::text('q', Input::get('q'), array('class' => 'form-control', 'placeholder' => 'Hesap adı girin..')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
            {{ Form::close() }}
        </div>
    </div>


    <div class="col-md-6">
        <div class="pull-right">
            @if( $user->role == 'admin'|| is_super_admin())
            <a href="{{ URL::route('bwatch.create', array()) }}" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Hesap Bağlayın</a>
            @endif
        </div>
    </div>


</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>No</th>
            <th>BW Client</th>
            <th>BW Kullanıcı</th>
            <th>Hesap Bilgisi</th>
            <th>Bağlantı</th>
            <th>Durum</th>
            <th>Geçerlilik Tarihi</th>
            <th>Oluşturma Tarihi</th>
            <th>Son Güncelleme</th>
            <th class="text-right">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        @if (count($bwatchs) > 0)
        @foreach ($bwatchs as $bwatch)

        <?php
        switch ($bwatch->is_active) {
            case '0': $label_active = 'label-primary';
                $text_active = 'Bağlantı yok';
                break;
            case '1': $label_active = 'label-success';
                $text_active = 'Bağlandı';
                break;
            default: $label_active = 'label-primary';
                $text_active = 'Bağlantı yok';
        }


        switch ($bwatch->status) {
            case '0': $label_status = 'label-primary';
                $text_status = 'Çalışmıyor';
                break;
            case '1': $label_status = 'label-success';
                $text_status = 'Çalışıyor';
                break;
            default: $label_status = 'label-primary';
                $text_status = 'Çalışmıyor';
        }
        ?>

        <tr>

            <td><span class="label label-default"># {{ $bwatch->id }}</span></td>
            <td><span class="label label-info">{{ $bwatch->client_name }}</span></td>
            <td><span class="label label-info">{{ $bwatch->username }}</span></td>

            <td><span class="label label-primary">{{ Account::find($bwatch->account_id)->name }}</span></td>           
            <td><span class="label {{$label_active}}">{{$text_active}}</span></td>
            <td><span class="label {{$label_status}}">{{ $text_status }}</span></td>
            @if($bwatch->is_active == 1)
            <td><span class="label label-default">{{ \Carbon\Carbon::createFromTimestamp($bwatch->created_at->getTimestamp())->addSeconds($bwatch->expires_in) }}</span></td>
            @else
            <td><span class="label label-primary">{{ $text_status }}</span></td>
            @endif

            <td><span class="label label-default">{{ $bwatch->created_at }}</span></td>
            <td><span class="label label-default">{{ $bwatch->updated_at }}</span></td>


            <td class="text-right" width="150">
                {{ Form::open(array('role' => 'form', 'route' => array('bwatch.destroy', $bwatch->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete', 'id' => $bwatch->id)) }}   
     <!--pause/play-->
        @if( $user->role == 'admin'|| is_super_admin())

        @if($bwatch->status == 1)
        {{ HTML::linkRoute('bwatch.pause', '', array($bwatch->id), array('class' => 'btn-sm btn-danger fa fa-pause tooltip-show', 'title' => 'Durdur')) }}
        @else
        {{ HTML::linkRoute('bwatch.run', '', array($bwatch->id), array('class' => 'btn-sm btn-info fa fa-play tooltip-show', 'title' => 'Çalıştır')) }}
        @endif

        @endif
        <!--pause/play end-->

        {{ HTML::linkRoute('bwatch.edit', '', array($bwatch->id), array('class' => 'btn-sm btn-default fa fa-gears tooltip-show', 'title' => 'Kurallar')) }}

        {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-sm btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}

        {{ Form::close() }}
        </td>

        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="8" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
        </tr>
        @endif
        </tbody>
</table>

{{ $bwatchs->links() }}
@stop