@extends('adminlte::page')


@section('template_fastload_css')
@endsection

@section('template_title')
    {!! trans('hyplast.showing-storage-title') !!}
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
                    <div class="card-body">
                        <a class="btn btn-info" href="{{route("storages.transport")}}">
                            {!! trans('hyplast.buttons.transport') !!}
                        </a>
                        <a class="btn btn-warning" href="{{route("storages.transfer")}}">
                            {!! trans('hyplast.buttons.transfer') !!}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {!! trans('hyplast.showing-storage') !!}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive machine-table">
                            <table id="data-table" class="table table-striped table-bordered shadow-lg table-sm data-table display" style="width:100%">
                                <caption id="machine_count" name="machine_count">
                                    {{ trans_choice('hyplast.machines-table.caption', 1, ['machinescount' => $storages->count()]) }}
                                </caption>
                                <thead class="thead">
                                    <tr>
                                        <th>{!! trans('hyplast.table.id2') !!}</th>
                                        <th>{!! trans('hyplast.table.date_production') !!}</th>
                                        <th>{!! trans('hyplast.table.time_production') !!}</th>
                                        <th>{!! trans('hyplast.table.requisition') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md">{!! trans('hyplast.table.product') !!}</th>
                                        <th class="text-center">{!! trans('hyplast.table.boxs') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md text-center">{!! trans('hyplast.table.user_production') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md text-center">{!! trans('hyplast.table.transfer') !!}</th>
                                        <th class="hidden-sm hidden-xs hidden-md">{!! trans('hyplast.table.batch') !!}</th>
                                        <th>{!! trans('hyplast.table.actions') !!}</th>
                                        <th class="no-search no-sort"></th>
                                        <th class="no-search no-sort"></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts.datatables.datatables-storages')

    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    <script type="text/javascript">
        function deleteConfirmation(id) {
            swal({
                title: "Eliminar?",
                text: "Por Favor, asegurese y luego Confirme!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Si, Eliminar Registro!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        type: 'POST',
                        url: "{{url('/storages/delete')}}/" + id,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("Done!", results.message, "success");
                                window.location = "/storages";
                            } else {
                                swal("Error!", results.message, "error");
                            }
                        }
                    });
                } else {
                    e.dismiss;
                }
            }, function (dismiss) {
                return false;
            })
        }
    </script>

@endsection


