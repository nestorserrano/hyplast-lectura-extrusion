@extends('adminlte::page')


@section('template_fastload_css')
@endsection

@section('template_title')
    {!! trans('hyplast.showing-all-machines') !!}
@endsection

@section('template_linked_css')
    @if(config('hyplast.enabledDatatablesJs'))
        <link rel="stylesheet" type="text/css" href="{{ config('hyplast.datatablesCssCDN') }}">
    @endif
    <style type="text/css" media="screen">
        .machine-table {
            border: 0;
        }
        .machine-table tr td:first-child {
            padding-left: 15px;
        }
        .machine-table tr td:last-child {
            padding-right: 15px;
        }
        .machine-table.table-responsive,
        .machine-table.table-responsive table {
            margin-bottom: 0;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid">
         <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {!! trans('hyplast.showing-all-machines') !!}
                            </span>


                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row align-items-top">
                            <div class="col-sm-6 align-self-top">
                                <div class="form-group has-feedback row {{ $errors->has('code') ? ' has-error ' : '' }}">
                                    {!! Form::label('code', trans('forms.create_machine_label_code'), array('class' => 'col-md-3 control-label')); !!}
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            {!! Form::text('code', NULL, array('id' => 'code', 'class' => 'form-control', 'placeholder' => trans('forms.create_machine_ph_code'))) !!}
                                            <div class="input-group-append">
                                                <label for="code" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_machine_icon_code') }}" aria-hidden="true"></i>
                                                </label>
                                                <a class="btn btn-info" type="button" id="abrirModal" data-toggle="modal" data-target="#cameraScanner" data-keyboard="true" data-backdrop="static" onclick="camara()">
                                                    {!! trans('hyplast.buttons.camera') !!}
                                                </a>
                                            </div>
                                        </div>
                                        @if ($errors->has('code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 align-self-top">
                                <div class="form-group has-feedback row {{ $errors->has('barcode') ? ' has-error ' : '' }}">
                                    {!! Form::label('barcode', trans('forms.create_machine_label_code'), array('class' => 'col-md-3 control-label')); !!}
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            {!! Form::text('barcode', NULL, array('id' => 'barcode', 'class' => 'form-control', 'placeholder' => trans('forms.create_machine_ph_code'))) !!}
                                            <div class="input-group-append">
                                                <label for="barcode" class="input-group-text">
                                                    <i class="fa fa-fw {{ trans('forms.create_machine_icon_code') }}" aria-hidden="true"></i>
                                                </label>
                                            </div>
                                        </div>
                                        @if ($errors->has('barcode'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('barcode') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    @include('modals.modal-scanner')
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')

    @include('scripts.save-modal-script')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    @if(config('hyplast.enableSearch'))
        @include('scripts.searchs.search-categorymachines')
    @endif
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script type="text/javascript">
        function camara() {
            var resultContainer = document.getElementById('qr-reader-results');
            var lastResult, countResults = 0;
            var myModalEl = document.getElementById('cameraScanner');
            var modal = new bootstrap.Modal(myModalEl);
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                if (decodedText !== lastResult) {
                    ++countResults;
                    lastResult = decodedText;
                    document.getElementById("code").value = lastResult;
                    results();
                    html5QrCode.stop().then((ignore) => {
                        $("#close1").click();
                        $('#cameraScanner').modal('hide')
                    }).catch((err) => {
                        alert("Fallo el cierre de la cámara, actualize la página")
                    });
                }
            };
            const html5QrCode = new Html5Qrcode("qr-reader");
            const config = { fps: 10,qrbox: { width: 250, height: 250 }};
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback).catch((err) => {
                swal({
                    title: "Error al Leer la cámara",
                    text: "Falla iniciando la cámara, verifique los permisos o si su dispositivo posee una cámara instalada. Actualice!",
                    type: "error",
                    showConfirmButton: true,
                    confirmButtonText: "Aceptar",
                    position: 'center',
                    toast: false,
                }).then(function (e) {
                    if (e.value === true) {
                        $("#close1").click();
                        $('#cameraScanner').modal('hide')
                    } else {
                        e.dismiss;
                    }
                }, function (dismiss) {
                    return false;
                })

            });

        }

        function results() {
            document.getElementById("barcode").value = document.getElementById("code").value;
            clearData();
        }

        function clearData() {
            document.getElementById("code").value = "";
        }

        $("#code").change(function(e){
            results();
        });

        $("#close1").click(function() {
            $("#cameraScanner").modal("hide");
        });
    </script>


@endsection
