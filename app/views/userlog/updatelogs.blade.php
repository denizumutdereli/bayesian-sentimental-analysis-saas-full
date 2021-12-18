@extends('layouts.master')

@section('title', 'Version')


@section('content')


<div class="page-header" id="user">
    <h2>Version Güncellemeleri
        <div class="pull-right">
            <a class="btn btn-default" href="/ticket/create"><span class="fa fa-plus"></span> Hata Bildir</a>
        </div>
   </h2>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Version</th>
            <th>Durum</th>
            <th>Açıklama</th>
            <th class="text-right">Tarih</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>V 1.0.4</td>
            <td><span class="label label-success">stable</span></td>
            <td>İstatistik verileri eklendi</td>
            <td class="text-right">28.11.2014</td>
        </tr>
        <tr>
            <td>V 1.0.3</td>
            <td><span class="label label-success">stable</span></td>
            <td>Arama modülü kullanılabilrliği artırıldı, herhangi bir domain için default değeri atanabilir duruma getirildi, özel domain seçenği eklendi</td>
            <td class="text-right">26.11.2014</td>
        </tr>
        <tr>
            <td>V 1.0.2</td>
            <td><span class="label label-success">stable</span></td>
            <td>Ticket modülü eklendi</td>
            <td class="text-right">06.11.2014</td>
        </tr>
        <tr>
            <td>V 1.0.1</td>
            <td><span class="label label-success">stable</span></td>
            <td>Arayüz değişikliği yapıldı filtreler sol tarafa alındı</td>
            <td class="text-right">05.11.2014</td>
        </tr>
        <tr>
            <td>V 1.0.0</td>
            <td><span class="label label-success">stable</span></td>
            <td>-</td>
            <td class="text-right">03.11.2014</td>
        </tr>
    </tbody>
</table>

@stop