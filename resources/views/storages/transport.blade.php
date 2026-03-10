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
        <div class="col-lg-10 offset-lg-1">
           <div class="card">
                <div class="card-header">
                    <div>
                        {!! trans('hyplast.showing-transport') !!}
                        <div class="pull-right">
                            <a href="{{ route('storages') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="top" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                {!! trans('hyplast.buttons.back-to-machines') !!}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="has-text-centered pt-6 pb-6">
                        <small>Para agregar una paleta puede de digitar el código de barras en el campo <strong>"Código"</strong> y luego presionar &nbsp; <strong class="is-uppercase">enter</strong>. &nbsp; También puede agregar la paleta mediante la cámara del dispositivo, pulsando el botón &nbsp; <strong class="is-uppercase"><i class="fas fa-camera"></i>. &nbsp;</strong> Igualmente puede escanear el código de barras desde el lector haciendo click en el campo <strong>"Código"</strong> y escaneando el código desde la Paleta</small>
                    </p>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            @if(count($errors)>0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{$error}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-top">
                        <div class="col-sm-6 align-self-top">
                            <div class="form-group has-feedback row {{ $errors->has('batch') ? ' has-error ' : '' }}">
                                {!! Form::label('batch', trans('forms.create_machine_label_code'), array('class' => 'col-md-3 control-label')); !!}
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {!! Form::text('batch', NULL, array('id' => 'batch', 'class' => 'form-control', 'placeholder' => trans('forms.create_machine_ph_code'))) !!}
                                        <div class="input-group-append">
                                            <label for="batch" class="input-group-text">
                                                <i class="fa fa-fw {{ trans('forms.create_machine_icon_code') }}" aria-hidden="true"></i>
                                            </label>
                                            <a class="btn btn-info" type="button" id="abrirModal" data-toggle="modal" data-target="#cameraScanner" data-keyboard="true" data-backdrop="static" onclick="camara()">
                                                {!! trans('hyplast.buttons.camera') !!}
                                            </a>
                                        </div>
                                    </div>
                                    @if ($errors->has('batch'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('batch') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('modals.modal-scanner')
                    {!! Form::open(array('url'=>'transport/register', 'method'=>'GET', 'autocomplete'=>'off', 'id' => 'form')) !!}
                    {!! csrf_field() !!}
                    <div class="table-responsive storage-table">
                        <table class="table table-striped table-sm data-table" id="detalles">
                            <thead style="background-color:#A9D0F5">
                                <th class="text-center">Opciones</th>
                                <th class="hidden-sm hidden-xs hidden-md text-center">Orden</th>
                                <th class="hidden-sm hidden-xs hidden-md text-center">Código</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Recibido</th>
                            </thead>
                            <tbody id="table-body"></tbody>
                            <tfoot>
                                <th class="text-center"></th>
                                <th class="hidden-sm hidden-xs hidden-md text-center"></th>
                                <th class="hidden-sm hidden-xs hidden-md text-center"></th>
                                <th></th>
                                <th class="text-right">Bultos-></th>
                                <th class="text-center"><h4 id="total">0</h4><input type="hidden" name="total" id="total"></th>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-sm-6 align-self-center" style="text-center">
                            <button class="btn btn-success btn-save guardar" name="guardar" id="guardar" type="submit" onclick="confirm_form()">{!! trans('forms.save-create') !!}</button>
                            <button class="btn btn-danger btn-cancel" type="reset" onclick="cancelTransfer()">{!! trans('hyplast.buttons.cancel') !!}</button>
                            <button class="btn btn-warning btn-refresh recoverBtn" name="recoverBtn" id="recoverBtn" type="button" onclick="recoverTransfer()">{!! trans('hyplast.buttons.restore') !!}</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
           </div>
       </div>
    </div>
</div>




@endsection

@section('footer_scripts')

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.1.2/handlebars.min.js"></script>

    <script type="text/javascript">

        $(document).ready(function(){
            $("form").keypress(function(e) {
                if (e.which == 13) {
                    return false;
                }
            });
        });


        var cont = 0;
        subtotal=[];
        total = 0;
        let men1 = "";
        let cancel1 = false;

        $("#guardar").hide();

        $("#recoverBtn").hide();

        $("#batch").change(results);

        function evaluar()
        {
            if (total>0)
            {
                $("#guardar").show();

            }
            else
            {
                $("#guardar").hide();
            }
        }



        function results()
        {

            var id = document.getElementById("batch").value;
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type:'POST',
                url: "{{ url('reqstorage') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    if (result.length != 0) {
                        product = result.product_id;
                        quantity = result.quantity;
                        machine = result.machine_id;
                        requisition = result.requisition_id;
                        batch = result.batch;
                        storage = result.id;
                        productcode = result.code;
                        productname = result.name;
                        received = result.quantity;
                        location_id = result.location_id;
                        dunnage = result.dunnage_size;
                        userp = result.user_production;
                        if (result.product_id!="" && result.quantity!="" && result.quantity>0)
                        {
                            subtotal[cont] = received;
                            total = total + subtotal[cont];
                            var fila ='<tr class="selected" id="fila'+cont+'"><td class="text-center"><button type="button" class="btn  btn-sm btn-danger btn-sm" onclick="eliminar('+cont+');"><i class="fa fa-trash-o fa-fw" aria-hidden="true"></i></button></td><td class="hidden-sm hidden-xs hidden-md text-center"><input type="hidden" name="requisition[]" value='+requisition+'>'+requisition+'</td><td class="hidden-sm hidden-xs hidden-md text-center" ><input type="hidden" name="code[]" value='+productcode+'>'+productcode+'</td><td><input type="hidden" name="productname[]" value='+productname+'>'+productname+'</td><td class="text-center"><input type="hidden" name="quantity[]" value='+quantity+'>'+quantity+'</td><td class="text-center"><input class="txt" type="number" style="width:50%" name="received[]" min="1" max="'+ received +'" value='+received+'></td><td><input type="hidden" name="machine[]" value='+machine+'></td><td><input type="hidden" name="storage[]" value='+storage+'></td><td><input type="hidden" name="batch[]" value='+batch+'></td></td><input type="hidden" name="product[]" value='+product+'></td><td><input type="hidden" name="pallet[]" value='+cont+'></td><td><input type="hidden" name="location_id[]" value='+location_id+'></td><td><input type="hidden" name="userp[]" value='+userp+'></td></tr>';
                            cont++;
                            $('#detalles').append(fila);
                            evaluar();
                            refreshRow();
                            refreshdata();
                        }
                        else
                        {
                            Swal.fire({
                                title: 'Error! Operación Cancelada',
                                text: 'Error al ingresar el detalle del Traslado, revise los datos de la Transferencia',
                                icon: 'error',
                                showCancelButton: false,
                                showLoaderOnConfirm: false,
                            });
                            document.getElementById("batch").value=""
                            refreshdata()
                            document.getElementById("batch").focus();
                        }
                            clearData();
                    } else {
                        Swal.fire({
                            title: 'Error! Operación Cancelada',
                            text: 'Al parecer este Código de Barras ya fue utilizado. Repita la lectura del código de Barras o contacte al Departamento de Tecnología',
                            icon: 'error',
                            showCancelButton: false,
                            showLoaderOnConfirm: false,
                        });
                        document.getElementById("batch").value=""
                        refreshdata()
                        document.getElementById("batch").focus();
                    };

                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        Swal.fire({
                            title: 'Error! Operación Cancelada',
                            text: 'No se pudo consultar los Productos en Piso! Comuniquese con el Departamento de Tecnología',
                            icon: 'error',
                            showCancelButton: false,
                            showLoaderOnConfirm: false,
                        });
                        document.getElementById("batch").value="";
                        refreshdata()
                        document.getElementById("batch").focus();
                    };
                },
            });
            if(cancel1 == true) {
                $("#recoverBtn").show();
                cancel1 == false;
            } else {
                $("#recoverBtn").hide();
            }

        }

        function refreshdata()
        {
            var x = "";
            $("table-body").html="";
            x.html = "";
            x = document.getElementById("table-body");
            copiarContenido(x.innerHTML);
         }

        function copiarContenido(men1) {
            navigator.clipboard.writeText(men1)
            .then(() => {
                console.log('Texto copiado al portapapeles')
            })
            .catch(err => {
                console.error('Error al copiar al portapapeles:', err)
            })
        }

        function recoverTransfer()
        {
            navigator.clipboard.readText()
            .then(text => {
                console.log('Texto del portapapeles:', text);
                $('#detalles').append(text);
                evaluar();
                refreshRow();
                men1 == "";
                navigator.clipboard.writeText("");
                $("#recoverBtn").hide();
            })
            .catch(err => {
                console.error('Error al leer del portapapeles:', err);
                $('#detalles').append(document.getElementById("recover").value);
                navigator.clipboard.writeText("");
                $("#recoverBtn").hide();
            })
            $("#guardar").show();
        }

        $('#detalles').on('input', ':input', function() {
            var value = $(this).val();
            total = 0;
            if (value.length > 0){
                $(".txt").each(function() {
                    total += parseInt(this.value);
                });
                $("#total").html(total);
            }
        });



        function eliminar(index)
        {

            $("#fila"+index).remove();
            refreshRow();
            evaluar();
            refreshdata();
		}

        function refreshRow()
        {
            total = 0;
            $(".txt").each(function() {
                total += parseInt(this.value);
                console.log(total);
            });
            $("#total").html(total);
        }

        function cancelTransfer()
        {
            var n;
            n = 0;

            $(".txt").each(function() {
                n = n + 1;
                console.log(n);
            });

            if(n > 0)
            {
                Swal.fire({
                    title: "¿Está seguro?",
                    html: "Se eliminará toda la información de la recepción. todas las Paletas ingresadas deberá, nuevamente escanearlas y llenar de nuevo todo el formulario",
                    showCancelButton: true,
                    confirmButtonText: "Si, estoy Seguro!",
                    cancelButtonText: "No, cancelar!",
                    reverseButtons: !0,
                    icon: 'error',
                }).then((result) => {
                    refreshdata()
                    if (result.value) {
                        Swal.fire({
                            title: "Eliminar todo El Registro",
                            html: "Confirma Bajo su responsabilidad que eliminará todo el formulario y se compromete a cargar todas las paletas ya registradas?",
                            showCancelButton: true,
                            confirmButtonText: "Si, Eliminar Todo!",
                            cancelButtonText: "No, cancelar!",
                            reverseButtons: !0,
                        }).then((result) => {
                            refreshdata()
                            if (result.value) {
                                Swal.fire({
                                    title: 'Error! Operación Cancelada',
                                    text: 'Cancelada la Operación, se elimina toda la recepción!',
                                    icon: 'error',
                                    showCancelButton: false,
                                    showLoaderOnConfirm: false,
                                });
                                $("#detalles > tbody").empty();
                                $("#total").html(0);
                                document.getElementById("batch").value="";
                                document.getElementById("batch").focus();
                                $("#recoverBtn").show();
                                $("#guardar").hide();
                            }
                        });
                    }
                });
            }
            else
            {
                Swal.fire({
                    title: 'No hay nada',
                    html: 'No hay nada que eliminar.',
                    icon: 'info',
                    showCancelButton: false,
                });
                $("#detalles > tbody").empty();
                $("#total").html(0);
                $("#recoverBtn").hide();
                $("#guardar").hide();
                document.getElementById("batch").value="";
                document.getElementById("batch").focus();
                refreshdata();
            }

        }

        function camara() {
            var resultContainer = document.getElementById('qr-reader-results');
            var lastResult, countResults = 0;
            var myModalEl = document.getElementById('cameraScanner');
            var modal = new bootstrap.Modal(myModalEl);
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                if (decodedText !== lastResult) {
                    ++countResults;
                    lastResult = decodedText;
                    document.getElementById("batch").value = lastResult;
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

        function clearData() {
            document.getElementById("batch").value = "";
        }

    </script>


@endsection

