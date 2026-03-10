@extends('adminlte::page')


@section('template_fastload_css')
@endsection

@section('template_title')
    {!! trans('hyplast.showing-all-products') !!}
@endsection

@section('template_linked_css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

@endsection
<!-- Meta -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i>
                        {!! trans('hyplast.showing-all-products') !!}
                    </h3>
                    {{-- Botón Nuevo Producto deshabilitado: Los productos se crean en Softland ERP --}}
                    {{-- <div class="card-tools">
                        <a class="btn btn-success btn-sm" href="/products/create">
                            <i class="fas fa-plus"></i> {!! trans('hyplast.buttons.create-new3') !!}
                        </a>
                    </div> --}}
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="filter_articulo">Código</label>
                            <input type="text" class="form-control form-control-sm" id="filter_articulo" placeholder="Buscar código...">
                        </div>
                        <div class="col-md-1">
                            <label for="filter_tipo">Tipo</label>
                            <select class="form-control form-control-sm" id="filter_tipo">
                                <option value="">Todos</option>
                                <option value="T">Terminado</option>
                                <option value="E">SemiElaborado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_grupo">Grupo</label>
                            <select class="form-control form-control-sm" id="filter_grupo">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_familia">Familia</label>
                            <select class="form-control form-control-sm" id="filter_familia">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_categoria">Categoría</label>
                            <select class="form-control form-control-sm" id="filter_categoria">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="filter_color">Color</label>
                            <select class="form-control form-control-sm" id="filter_color">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_material">Material</label>
                            <select class="form-control form-control-sm" id="filter_material">
                                <option value="">Todos</option>
                            </select>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="data-table" class="table table-bordered table-hover table-striped table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">{!! trans('hyplast.machines-table.code') !!}</th>
                                    <th>{!! trans('hyplast.machines-table.name') !!}</th>
                                    <th class="text-center">Grupo</th>
                                    <th class="text-center">{!! trans('hyplast.machines-table.family') !!}</th>
                                    <th class="text-center">{!! trans('hyplast.machines-table.category') !!}</th>
                                    <th class="text-center">{!! trans('forms.create_product_label_color') !!}</th>
                                    <th class="text-center">{!! trans('forms.create_product_label_material') !!}</th>
                                    <th class="text-center">Imagen</th>
                                    <th class="text-center">{!! trans('hyplast.machines-table.actions') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modals.modal-machine')
@include('modals.modal-supplies')
@include('modals.modal-imagenes-producto')


@endsection

@section('footer_scripts')
    @include('scripts.datatables.datatables-product')
    @include('scripts.save-modal-script')

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/i18n/es.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // Configurar Select2 para el combo de productos en modal supplies
            $('#product2').select2({
                language: "es",
                dropdownParent: $('#modalSupplies'),
                placeholder: '{{ trans("forms.create_product_label_supplie") }}',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: "{{ route('autocomplete.products') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        var tipo = $('#filter_tipo_articulo').val();
                        var grupo = $('#filter_grupo_supplie').val();
                        var proceso = $('#filter_proceso_supplie').val();

                        return {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            search: params.term,
                            q: params.term,
                            tipo: tipo,
                            grupo: grupo,
                            proceso: proceso
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: false
                },
                minimumInputLength: 0
            });

            // Cargar filtros del modal supplies SOLO cuando se abre el modal
            $('#modalSupplies').on('shown.bs.modal', function() {
                cargarFiltrosModalSupplies();
            });

            // Filtros en cascada - Cuando cambia Tipo de Artículo
            $(document).on('change', '#filter_tipo_articulo', function() {
                $('#filter_grupo_supplie').val('').trigger('change');
                $('#filter_proceso_supplie').val('').trigger('change');
                $('#product2').val(null).trigger('change');
            });

            // Cuando cambia Grupo
            $(document).on('change', '#filter_grupo_supplie', function() {
                $('#filter_proceso_supplie').val('').trigger('change');
                $('#product2').val(null).trigger('change');
            });

            // Cuando cambia Proceso
            $(document).on('change', '#filter_proceso_supplie', function() {
                $('#product2').val(null).trigger('change');
            });

            // Botón Agregar Insumo
            $(document).on('click', '#btnAgregarInsumo', function() {
                var productCode = $('#modal_result3').attr('data-product-code');
                var supplieId = $('#product2').val();
                var supplieName = $('#product2 option:selected').text();

                if (!supplieId) {
                    Swal.fire({
                        title: "Atención",
                        text: "Debe seleccionar un insumo",
                        icon: "warning",
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }

                // Guardar datos temporales y abrir modal de cantidad
                $('#input_cantidad_insumo').val('');
                $('#input_cantidad_insumo').data('product-code', productCode);
                $('#input_cantidad_insumo').data('supplie-id', supplieId);
                $('#modalCantidadInsumo').modal('show');

                setTimeout(function() {
                    $('#input_cantidad_insumo').focus();
                }, 500);
            });

            // Confirmar cantidad en el modal
            $(document).on('click', '#btnConfirmarCantidad', function() {
                var inputValue = $('#input_cantidad_insumo').val();
                var productCode = $('#input_cantidad_insumo').data('product-code');
                var supplieId = $('#input_cantidad_insumo').data('supplie-id');

                if (!inputValue || inputValue.trim() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe ingresar una cantidad'
                    });
                    return;
                }

                var quantity = parseFloat(inputValue);
                if (isNaN(quantity) || quantity <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe ingresar una cantidad válida mayor a 0'
                    });
                    return;
                }

                // Agregar el insumo vía AJAX
                $.ajax({
                    url: "{{ url('products/attachsupplie') }}/" + productCode + "/" + supplieId + "/" + quantity,
                    type: 'GET',
                    success: function(response) {
                        // Cerrar modal de cantidad
                        $('#modalCantidadInsumo').modal('hide');

                        // Recargar la tabla de insumos
                        recargarTablaInsumos(productCode);

                        // Limpiar el select2 para poder agregar otro insumo
                        $('#product2').val(null).trigger('change');

                        // Mostrar mensaje de éxito
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message || 'Insumo agregado correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#modalCantidadInsumo').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo agregar el insumo. Intente nuevamente.'
                        });
                    }
                });
            });

            // Permitir Enter en el input de cantidad
            $(document).on('keypress', '#input_cantidad_insumo', function(e) {
                if (e.key === 'Enter') {
                    $('#btnConfirmarCantidad').click();
                }
            });
        });

        // Función para recargar la tabla de insumos (GLOBAL)
        window.recargarTablaInsumos = function(productCode) {
            $.ajax({
                type: 'POST',
                url: "{{ url('productssupplies') }}/" + productCode,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    $('#modal_result3').html(data.tabla);
                },
                error: function(xhr) {
                    // Error silencioso
                }
            });
        };

        // Función ESPECÍFICA para cargar los filtros del MODAL de supplies
        function cargarFiltrosModalSupplies() {
            // Cargar tipos de artículos activos
            $.ajax({
                url: "{{ url('products/tipos-activos') }}",
                type: 'GET',
                success: function(data) {
                    if (data && Array.isArray(data)) {
                        var options = '<option value="">Todos</option>';
                        $.each(data, function(index, item) {
                            options += '<option value="' + item.code + '">' + item.name + '</option>';
                        });
                        $('#filter_tipo_articulo').html(options);
                    }
                },
                error: function(xhr, status, error) {
                    // Error silencioso
                }
            });

            // Cargar Grupos (CLASIFICACION_1) para el MODAL
            $.ajax({
                url: "{{ url('products/clasificaciones/1') }}",
                type: 'GET',
                success: function(data) {
                    if (data && Array.isArray(data)) {
                        var options = '<option value="">Todos</option>';
                        $.each(data, function(index, item) {
                            options += '<option value="' + item.CLASIFICACION + '">' + item.DESCRIPCION + '</option>';
                        });
                        $('#filter_grupo_supplie').html(options);
                    }
                },
                error: function(xhr, status, error) {
                    // Error silencioso
                }
            });

            // Cargar Procesos (CLASIFICACION_2) para el MODAL
            $.ajax({
                url: "{{ url('products/clasificaciones/2') }}",
                type: 'GET',
                success: function(data) {
                    if (data && Array.isArray(data)) {
                        var options = '<option value="">Todos</option>';
                        $.each(data, function(index, item) {
                            options += '<option value="' + item.CLASIFICACION + '">' + item.DESCRIPCION + '</option>';
                        });
                        $('#filter_proceso_supplie').html(options);
                    }
                },
                error: function(xhr, status, error) {
                    // Error silencioso
                }
            });
        }

        // Función para eliminar insumo
        window.deleteattach = function(recordId) {
            Swal.fire({
                title: '¿Eliminar insumo?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('products/detachsupplie') }}/" + recordId,
                        type: 'GET',
                        success: function(response) {
                            if (response.success) {
                                // Recargar tabla
                                var productCode = $('#modal_result3').attr('data-product-code');
                                recargarTablaInsumos(productCode);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: response.message || 'Insumo eliminado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'No se pudo eliminar el insumo'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al eliminar el insumo'
                            });
                        }
                    });
                }
            });
        };
    </script>

    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    @if(config('hyplast.enableSearch'))
        @include('scripts.searchs.search-products')
    @endif

@endsection
