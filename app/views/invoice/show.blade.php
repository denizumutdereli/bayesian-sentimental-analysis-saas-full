@extends('layouts.master')

@section('title', 'Kelime Kategorisini Güncelle')


@section('content')


<?php

echo 7/3;
//phpinfo();
?>

<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="invoice-title">
                <h3><a href="{{ URL::route('invoice.index', array()) }}" class="btn btn-default"><span class="fa fa-arrow-left"></span> Tüm Faturalar</a></h3>
                <h2 class="pull-right">Fatura No: {{ $invoice->id}}</h2>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-6">
                    <address>
                        <strong>Son Güncelleme:</strong><br>
                        {{$invoice->updated_at}}<br>
                        
                    </address>
                </div>
                <div class="col-xs-6 text-right">
                    <address>
                        <strong>Fatura Dönemi:</strong><br>
                        {{$invoice->created_at}}<br>
                        {{$invoice->payment_date}}<br>
                        {{ User::find($invoice->created_by)->email}}
                    </address>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>Fatura Detayları</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td><strong>Birim</strong></td>
                                    <td class="text-center"><strong>Fiyat</strong></td>
                                    <td class="text-center"><strong>Miktar</strong></td>
                                    <td class="text-right"><strong>Toplam</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                               
                                @foreach($details as $key => $val)
                                
                                <?php
                                $params = explode(':', $val);
                                 ?>
                                <tr>
                                    <td>{{$key}}</td>
                                    <td class="text-center">$ {{$params[1]}} </td>
                                    <td class="text-center">{{$params[0]}}</td>
                                    <td class="text-right">$ {{$val * $params[1]}}</td>
                                </tr>
                                
                                @endforeach
                                 
                                <tr>
                                    <td class="no-line"></td>
                                    <td class="no-line"></td>
                                    <td class="no-line text-center"><strong>Total</strong></td>
                                    <td class="no-line text-right">$ {{ $invoice->amount}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@stop



@section('style')
<style>
    .invoice-title h2, .invoice-title h3 {
        display: inline-block;
    }

    .table > tbody > tr > .no-line {
        border-top: none;
    }

    .table > thead > tr > .no-line {
        border-bottom: none;
    }

    .table > tbody > tr > .thick-line {
        border-top: 2px solid;
    }

</style>
@stop
