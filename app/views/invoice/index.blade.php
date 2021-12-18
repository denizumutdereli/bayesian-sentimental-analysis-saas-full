@extends('layouts.master')

@section('title', 'Faturalar')


@section('content')


<div class="page-header" id="user">
    <h2>Faturalar</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('invoice' => 'form', 'route' => array('invoice.index'), 'method' => 'GET', 'class' => '')) }}
            <div class="input-group">
                {{ Form::text('q', Input::get('q'), array('class' => 'form-control', 'placeholder' => 'Fatura no giriniz..')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                </span>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Fatura No</th>
            <th>Hesap Bilgisi</th>
            <th>Oluşturma Tarihi</th>
            <th>Son Güncelleme</th>
            <th>Dönem</th>
            <th>Toplam Tutar</th>
            <th class="text-right">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        @if (count($invoices) > 0)
        @foreach ($invoices as $invoice)

        <tr>

            <td><span class="label label-default"># {{ $invoice->id }}</span></td>
            <td><span class="label label-success">{{ Account::find($invoice->account_id)->name }}</span></td>
            <td><span class="label label-default">{{ $invoice->created_at }}</span></td>
            <td><span class="label label-warning">{{ $invoice->updated_at }}</span></td>
            <td><span class="label label-default">{{ $invoice->payment_date }}</span></td>
            <td><span class="label label-default">$ {{ $invoice->amount }}</span></td>  
            <td class="text-right" class="text-right">
                {{ Form::open(array('role' => 'form', 'route' => array('invoice.show', $invoice->id), 'method' => 'GET', 'class' => 'pull-right')) }}
                <fieldset>
                    {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-info fa fa-money tooltip-show', 'title' => 'Detaylar')) }}
                </fieldset>
                {{ Form::close() }}
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="7" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
        </tr>
        @endif
    </tbody>
</table>

{{ $invoices->links() }}
@stop