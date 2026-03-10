@extends('adminlte::page')

@section('template_title')
    Producto: {{ $product->ARTICULO }}
@endsection

@section('content_header')
<style>
    .hover-shadow:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(0,123,255,0.5) !important;
    }
    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
        z-index: 10;
    }
    /* Asegurar que las tablas tengan z-index menor que el chat */
    .table {
        position: relative;
        z-index: 1;
    }
    /* Widget de chat debe tener z-index alto */
    #tidio-chat,
    .tidio-chat-iframe,
    [id*="tidio"],
    [class*="tidio"] {
        z-index: 999999 !important;
    }
    /* Código de barras centrado */
    .barcode-container {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .barcode-container svg {
        margin: 0 auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Artículo: {{ $product->ARTICULO }}</h4>
                        <a href="{{ url('products') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Información Básica con Imagen a la Derecha --}}
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="border-bottom pb-2">Información General</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Código:</th>
                                            <td>{{ $product->ARTICULO }}</td>
                                        </tr>
                                        <tr>
                                            <th>Descripción:</th>
                                            <td>{{ $product->DESCRIPCION }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tipo:</th>
                                            <td>
                                                <span class="badge badge-primary">{{ $product->TIPO }}</span>
                                                {{ $product->type_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                @if($product->ACTIVO === 'S')
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Código de Barras:</th>
                                            <td>{{ $product->CODIGO_BARRAS ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Código Barras Venta:</th>
                                            <td>{{ $product->CODIGO_BARRAS_VENT ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unidad Almacén:</th>
                                            <td>{{ $product->UNIDAD_ALMACEN ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unidad Venta:</th>
                                            <td>{{ $product->UNIDAD_VENTA ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Imagen y Código de Barras a la Derecha --}}
                        <div class="col-md-4">
                            <div class="text-center sticky-top" style="top: 20px;">
                                @php
                                    $fotoPrincipal = $product->foto_principal;
                                    $totalFotos = $product->fotos->count();
                                @endphp

                                @if($fotoPrincipal && $fotoPrincipal->FOTO)
                                    <div class="position-relative mb-3 producto-imagen-principal"
                                         style="cursor: pointer;"
                                         onclick="mostrarImagenesProducto('{{ $product->ARTICULO }}')"
                                         data-codigo="{{ $product->ARTICULO }}">
                                        <img src="{{ $fotoPrincipal->foto_url }}"
                                             alt="{{ $product->DESCRIPCION }}"
                                             class="img-thumbnail hover-shadow"
                                             style="width: 100%; max-width: 250px; height: auto; transition: transform 0.2s;">
                                        @if($totalFotos > 1)
                                            <div class="mt-2">
                                                <span class="badge badge-info">
                                                    <i class="fas fa-images"></i> Ver {{ $totalFotos }} foto{{ $totalFotos > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-image"></i> 1 foto
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <img src="{{ asset('images/no_image_available.png') }}"
                                             alt="{{ $product->DESCRIPCION }}"
                                             class="img-thumbnail"
                                             style="width: 100%; max-width: 250px; height: auto;">
                                        <div class="mt-2">
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-image-slash"></i> Sin imagen
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Código de barras --}}
                                @if($product->CODIGO_BARRAS_VENT)
                                    <div class="mt-3 p-3 bg-white border rounded barcode-container" style="position: relative; z-index: 1;">
                                        {!! $barra->getBarcodeHTML($product->CODIGO_BARRAS_VENT, "EAN13") !!}
                                        <small class="text-muted d-block mt-2">{{ $product->CODIGO_BARRAS_VENT }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Clasificaciones --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Clasificaciones</h5>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Clasificación 1 (Grupo):</th>
                                    <td>
                                        @if($product->CLASIFICACION_1)
                                            <strong>{{ $product->CLASIFICACION_1 }}</strong>
                                            @if(isset($clasificaciones['grupo']) && $clasificaciones['grupo'])
                                                - {{ $clasificaciones['grupo']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Clasificación 2 (Proceso):</th>
                                    <td>
                                        @if($product->CLASIFICACION_2)
                                            <strong>{{ $product->CLASIFICACION_2 }}</strong>
                                            @if(isset($clasificaciones['proceso']) && $clasificaciones['proceso'])
                                                - {{ $clasificaciones['proceso']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Clasificación 3 (Familia):</th>
                                    <td>
                                        @if($product->CLASIFICACION_3)
                                            <strong>{{ $product->CLASIFICACION_3 }}</strong>
                                            @if(isset($clasificaciones['familia']) && $clasificaciones['familia'])
                                                - {{ $clasificaciones['familia']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Clasificación 4 (Categoría):</th>
                                    <td>
                                        @if($product->CLASIFICACION_4)
                                            <strong>{{ $product->CLASIFICACION_4 }}</strong>
                                            @if(isset($clasificaciones['categoria']) && $clasificaciones['categoria'])
                                                - {{ $clasificaciones['categoria']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Clasificación 5 (Material):</th>
                                    <td>
                                        @if($product->CLASIFICACION_5)
                                            <strong>{{ $product->CLASIFICACION_5 }}</strong>
                                            @if(isset($clasificaciones['material']) && $clasificaciones['material'])
                                                - {{ $clasificaciones['material']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Clasificación 6 (Color):</th>
                                    <td>
                                        @if($product->CLASIFICACION_6)
                                            <strong>{{ $product->CLASIFICACION_6 }}</strong>
                                            @if(isset($clasificaciones['color']) && $clasificaciones['color'])
                                                - {{ $clasificaciones['color']->DESCRIPCION }}
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Peso y Volumen --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Peso y Volumen</h5>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <th width="50%">Peso Neto:</th>
                                    <td>{{ $product->PESO_NETO ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Peso Bruto:</th>
                                    <td>{{ $product->PESO_BRUTO ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <th width="50%">Volumen:</th>
                                    <td>{{ $product->VOLUMEN ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Bultos:</th>
                                    <td>{{ $product->BULTOS ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Campos Personalizados U_ --}}
                    @if($product->U_MEDIDA || $product->U_DIAMETRO || $product->U_CAJA)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Información de Producto</h5>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Medida:</th>
                                    <td>{{ $product->U_MEDIDA ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Diámetro:</th>
                                    <td>{{ $product->U_DIAMETRO ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Caja:</th>
                                    <td>{{ $product->U_CAJA ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Peso Unidad:</th>
                                    <td>{{ $product->U_PESO_UNIDAD ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Cant. Paquete:</th>
                                    <td>{{ $product->U_CANT_PAQ ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Paquete por Caja:</th>
                                    <td>{{ $product->U_PAQ_CAJA ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Unidades por Caja:</th>
                                    <td>{{ $product->U_UNID_CAJA ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Cajas por Paleta:</th>
                                    <td>{{ $product->U_CAJAS_PALETA ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Información Adicional --}}
                    @if($product->UBICACION || $product->FACTOR_EMPAQUE)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Información Adicional</h5>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Ubicación:</th>
                                    <td>{{ $product->UBICACION ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Factor Empaque:</th>
                                    <td>{{ $product->FACTOR_EMPAQUE ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Empaque Estándar:</th>
                                    <td>{{ $product->EMPAQUE_STD ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Punto de Pedido:</th>
                                    <td>{{ $product->PUNTO_DE_PEDIDO ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Botones de Acción --}}
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('products.edit', $product->ARTICULO) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Editar
                            </a>
                            <a href="{{ url('products') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Carrusel de Imágenes --}}
<div class="modal fade" id="modalImagenesProducto" tabindex="-1" role="dialog" aria-labelledby="modalImagenesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalImagenesLabel">
                    <i class="fas fa-images"></i> Imágenes del Producto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="cerrarModalImagenes()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa;">
                <div id="carouselImagenesProducto" class="carousel slide" data-interval="false">
                    <div class="carousel-inner" id="carouselImagenesContent" style="min-height: 400px; background: #000;">
                        {{-- Las imágenes se cargarán dinámicamente aquí --}}
                        <div class="text-center p-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-white"></i>
                            <p class="mt-2 text-white">Cargando imágenes...</p>
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carouselImagenesProducto" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselImagenesProducto" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Siguiente</span>
                    </a>
                </div>
                <div class="text-center mt-3">
                    <span id="contadorImagenes" class="badge badge-info badge-lg" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-images"></i> Cargando...
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="cerrarModalImagenes()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Definir funciones en el scope global
window.mostrarImagenesProducto = function(codigoProducto) {
    console.log('=== INICIANDO FUNCIÓN ===');
    console.log('Código producto:', codigoProducto);
    console.log('jQuery disponible:', typeof $ !== 'undefined');
    console.log('Modal element exists:', $('#modalImagenesProducto').length > 0);

    // Detener cualquier carrusel previo
    try {
        $('#carouselImagenesProducto').carousel('dispose');
    } catch(e) {
        console.log('Sin carrusel previo');
    }

    // Mostrar loading
    $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x text-white"></i><p class="mt-2 text-white">Cargando imágenes...</p></div>');

    // Intentar abrir modal de múltiples formas
    try {
        $('#modalImagenesProducto').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        console.log('Modal abierto con jQuery');
    } catch(e) {
        console.error('Error abriendo modal:', e);
        // Método alternativo
        $('#modalImagenesProducto').addClass('show').css('display', 'block');
        $('body').addClass('modal-open').append('<div class="modal-backdrop fade show"></div>');
        console.log('Modal abierto con método alternativo');
    }

    // Obtener imágenes del producto
    $.ajax({
        url: '/products/' + codigoProducto + '/imagenes',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('=== RESPUESTA AJAX ===', response);
            if (response.success && response.imagenes && response.imagenes.length > 0) {
                let carouselHTML = '';
                response.imagenes.forEach(function(imagen, index) {
                    carouselHTML += `
                        <div class="carousel-item ${index === 0 ? 'active' : ''}">
                            <img src="${imagen.url}"
                                 class="d-block w-100"
                                 alt="Imagen ${index + 1}"
                                 style="max-height: 500px; object-fit: contain; background: #000;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 5px;">
                                <p>Imagen ${index + 1} de ${response.total}</p>
                            </div>
                        </div>
                    `;
                });

                $('#carouselImagenesContent').html(carouselHTML);
                $('#contadorImagenes').html('<i class="fas fa-images"></i> ' + response.total + ' imagen' + (response.total > 1 ? 'es' : '') + ' disponible' + (response.total > 1 ? 's' : ''));

                // Reinicializar el carrusel
                setTimeout(function() {
                    try {
                        $('#carouselImagenesProducto').carousel({
                            interval: false,
                            wrap: true
                        });
                        console.log('Carrusel inicializado');
                    } catch(e) {
                        console.error('Error inicializando carrusel:', e);
                    }
                }, 200);
            } else {
                $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-image fa-3x text-muted"></i><p class="mt-2">No hay imágenes disponibles</p></div>');
                $('#contadorImagenes').html('<i class="fas fa-images"></i> 0 imágenes');
            }
        },
        error: function(xhr, status, error) {
            console.error('=== ERROR AJAX ===');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-exclamation-triangle fa-3x text-danger"></i><p class="mt-2">Error al cargar las imágenes</p><small>' + error + '</small></div>');
            $('#contadorImagenes').html('<i class="fas fa-exclamation-circle"></i> Error');
        }
    });
};

window.cerrarModalImagenes = function() {
    console.log('Cerrando modal');
    try {
        $('#carouselImagenesProducto').carousel('dispose');
    } catch(e) {}

    try {
        $('#modalImagenesProducto').modal('hide');
    } catch(e) {
        $('#modalImagenesProducto').removeClass('show').css('display', 'none');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }
};
</script>

@endsection

@section('footer_scripts')
<script>
// Función para mostrar el modal de imágenes
function mostrarImagenesProducto(codigoProducto) {
    console.log('Clic en imagen - Código:', codigoProducto);

    // Detener cualquier carrusel previo
    try {
        if (typeof $.fn.carousel !== 'undefined') {
            $('#carouselImagenesProducto').carousel('dispose');
        }
    } catch(e) {
        console.log('No hay carrusel previo para destruir');
    }

    // Mostrar loading
    $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x text-white"></i><p class="mt-2 text-white">Cargando imágenes...</p></div>');

    // Abrir modal usando método nativo de Bootstrap o jQuery
    var modalElement = document.getElementById('modalImagenesProducto');
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Bootstrap 5
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else if (typeof $.fn.modal !== 'undefined') {
        // Bootstrap 4 (AdminLTE)
        $('#modalImagenesProducto').modal('show');
    } else {
        // Fallback - método nativo
        $(modalElement).addClass('show').css('display', 'block');
        $('body').addClass('modal-open');
        $('.modal-backdrop').remove();
        $('body').append('<div class="modal-backdrop fade show"></div>');
    }

    // Obtener imágenes del producto
    $.ajax({
        url: '/products/' + codigoProducto + '/imagenes',
        type: 'GET',
        success: function(response) {
            console.log('Respuesta AJAX:', response);
            if (response.success && response.imagenes.length > 0) {
                let carouselHTML = '';
                response.imagenes.forEach(function(imagen, index) {
                    carouselHTML += `
                        <div class="carousel-item ${index === 0 ? 'active' : ''}">
                            <img src="${imagen.url}"
                                 class="d-block w-100"
                                 alt="Imagen ${index + 1}"
                                 style="max-height: 500px; object-fit: contain; background: #000;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 5px;">
                                <p>Imagen ${index + 1} de ${response.total}</p>
                            </div>
                        </div>
                    `;
                });

                $('#carouselImagenesContent').html(carouselHTML);
                $('#contadorImagenes').html('<i class="fas fa-images"></i> ' + response.total + ' imagen' + (response.total > 1 ? 'es' : '') + ' disponible' + (response.total > 1 ? 's' : ''));

                // Reinicializar el carrusel después de cargar las imágenes
                setTimeout(function() {
                    if (typeof $.fn.carousel !== 'undefined') {
                        $('#carouselImagenesProducto').carousel({
                            interval: false,
                            wrap: true
                        });
                    }
                }, 100);
            } else {
                $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-image fa-3x text-muted"></i><p class="mt-2">No hay imágenes disponibles</p></div>');
                $('#contadorImagenes').html('<i class="fas fa-images"></i> 0 imágenes');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error, xhr.responseText);
            $('#carouselImagenesContent').html('<div class="text-center p-5"><i class="fas fa-exclamation-triangle fa-3x text-danger"></i><p class="mt-2">Error al cargar las imágenes</p></div>');
            $('#contadorImagenes').html('<i class="fas fa-exclamation-circle"></i> Error');
        }
    });
}

function cerrarModalImagenes() {
    var modalElement = document.getElementById('modalImagenesProducto');

    // Destruir carrusel
    try {
        if (typeof $.fn.carousel !== 'undefined') {
            $('#carouselImagenesProducto').carousel('dispose');
        }
    } catch(e) {
        console.log('Error destruyendo carrusel');
    }

    // Cerrar modal
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Bootstrap 5
        var modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    } else if (typeof $.fn.modal !== 'undefined') {
        // Bootstrap 4 (AdminLTE)
        $('#modalImagenesProducto').modal('hide');
    } else {
        // Fallback - método nativo
        $(modalElement).removeClass('show').css('display', 'none');
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }
}

// Alternativa: Agregar evento click con jQuery al cargar la página
$(document).ready(function() {
    console.log('Document ready - Agregando event listeners');
    console.log('Bootstrap disponible:', typeof $.fn.modal !== 'undefined');
    console.log('jQuery version:', $.fn.jquery);

    // Evento click en la imagen usando jQuery (respaldo)
    $('.producto-imagen-principal').on('click', function(e) {
        e.preventDefault();
        var codigo = $(this).data('codigo');
        console.log('Clic con jQuery - Código:', codigo);
        mostrarImagenesProducto(codigo);
    });

    // Evento para cerrar modal con tecla ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalImagenes();
        }
    });

    // Ejemplo de uso de notificaciones
    // Después de 3 segundos, mostrar un mensaje de bienvenida (solo para prueba)
    setTimeout(function() {
        console.log('Sistema de notificaciones listo');
        // Puedes usar las notificaciones así:
        // window.notifyMessage('¡Hola! Sistema de notificaciones activado', 'Sistema Hyplast');
    }, 3000);
});

// Función para probar notificaciones (llamar desde consola)
window.testNotification = function() {
    window.notifyMessage('Esta es una notificación de prueba', 'Sistema Hyplast');
};

window.testNewRequisition = function() {
    window.hyplastNotifications.notifyNewRequisition('OP-00456');
};

window.testProductionComplete = function() {
    window.hyplastNotifications.notifyProductionComplete('Bobina PET 1000mm x 30mic', 1500);
};

window.testInventoryAlert = function() {
    window.hyplastNotifications.notifyInventoryAlert('Materia Prima PP', 'Bajo: 150kg');
};
</script>
@endsection
