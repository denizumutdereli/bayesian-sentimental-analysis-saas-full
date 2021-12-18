@extends('layouts.master')

@section('title', $source->name . ' Kaynak Dosyaları' )


@section('content')


<div class="page-header" id="upload">
    <h2>{{$source->name}} Kaynaklar</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="input-group col-md-8">
            {{ Form::open(array('role' => 'form', 'method' => 'GET', 'class' => '')) }}
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
            <button id="add-source" class="btn btn-default"><span class="fa fa-plus"></span> Yeni Ekle</button>
        </div>
    </div>
</div>
<hr/>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Hesap Bilgisi</th>
            <th>Kaynak Kategorisi</th>
            <th>Dosya Adı</th>
            <th>Toplam Kayıt</th>
            <th>Ekleyen</th>
            <th>Eklenme Tarihi</th>
            <th>Son Güncelleme Tarihi</th>
            <th class="text-right">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        @if (count($uploads) > 0)
        @foreach ($uploads as $upload)
        <tr>

            <td>{{ $upload->id }}</td>
            <td><span class="label label-success">{{ Account::find($upload->account_id)->name }}</span></td>
            <td><span class="label label-danger">{{ Source::find($upload->source_id)->name }}
                </span></td>
            <td>
                <a class="label label-info input" href="#" class="input" id="uploadName" data-pk="{{$upload->id}}" data-type="text" data-placement="right" data-title="Dosya ismini değiştirebilirsiniz.">{{ $upload->name }}</a>                
            </td>



            <td><span class="label label-default">{{ $upload->count }}</span></td>
            <td>
                <?php
                switch (User::find($upload->user_id)->role) {
                    case 'super': $label = 'label-danger';
                        break;
                    case 'admin': $label = 'label-warning';
                        break;
                    case 'moderator': $label = 'label-info';
                        break;
                    default: $label = 'label-default';
                }
                ?>
                <span class="label {{ $label }}">{{ User::find($upload->user_id)->email }}</span>
            </td>
            <td>{{ $upload->created_at }}</td>
            <td>{{ $upload->updated_at }}</td>

            <td class="text-right" class="text-right">
                {{ Form::open(array('role' => 'form', 'route' => array('upload.destroy', $upload->id), 'method' => 'DELETE', 'class' => 'pull-right form-delete')) }}
                <fieldset>
                  <!--  {{ HTML::linkRoute('upload.edit', '', array($upload->id), array('class' => 'btn btn-success fa fa-unlock tooltip-show', 'title' => 'Kilitle')) }}-->
                    {{ Form::button('', array('type' => 'submit', 'class' => 'btn btn-primary fa fa-trash-o tooltip-show', 'title' => 'Sil')) }}
                </fieldset>
                {{ Form::close() }}
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="9" class="text-center"><small><em>Kayıt bulunamadı!</em></small></td>
        </tr>
        @endif
    </tbody>
</table>

{{ $uploads->links() }} 

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog" tabindex="-1" role="dialog"  aria-hidden="true">
    {{ Form::open(array('role' => 'form', 'route' => 'upload.create', 'method' => 'PUT', 'files' => true, 'id'=>'uploadForm')) }}
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">CSV Yükleme</h4>
            </div>
            <div class="modal-body" id="modal-body">

                <input type="hidden" name="source_id" value="{{$source->id}}">
                <p>Lütfen yüklemek istediğiniz dosyayı seçin.</p>
                {{ Form::file('csvfile', array('class' => 'form-control-static')) }}


                <div class="checkbox">
                    <label class="checkbox-inline">
                        {{ Form::checkbox('firstrow', '1', Input::old('firstrow')) }} İlk satırda alan isimleri var.
                    </label>
                </div>

                <span class="">Örnek Excel dosyasını <a href="/uploads/source.xlsx">buradan</a> indirebilirsiniz.</span>



            </div>
            <div class="modal-footer">

                {{ Form::submit('Yükle', array('class' => 'btn btn-default btn-success')) }}

                <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>


<div class="loading loading-since text-center center-block" style="position: fixed;
     top: 50% !important;
     left: 50%;
     transform: translate(-50%, -50%);display: none;">
    <div class="loading-icon">
        <i class="fa fa-spin fa-spinner fa-3x"></i>
        <br/>
        <span class="loading-message" data-message="{{ Config::get('settings.loading.message') }}">Yükleniyor...</span>
    </div>
</div>

@stop


@section('style')
<style>
    #progress {
        display: none;
    }
    .progress-striped {
        position: relative;
    }
    .percent {
        position: absolute;
        display: none;
    }
    .percent .percent-count {
        font-weight: 700;
    }
</style>
@stop

@section('script')
{{ HTML::script('/assets/js/source.js') }}
@stop