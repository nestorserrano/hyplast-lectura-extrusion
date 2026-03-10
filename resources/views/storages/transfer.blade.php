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
                    <div class="card-header">
                        <div>
                            {!! trans('hyplast.showing-send') !!}
                            <div class="pull-right">
                                <a href="{{ route('storages') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="top" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">

                <div class="table-responsive machine-table">
                    <table id="data-table" class="table table-striped table-bordered shadow-lg table-sm data-table display" style="width:100%">
                        <thead class="thead">
                            <tr>
                                <th class="w-1 p-1">{!! trans('hyplast.table.details') !!}</th>
                                <th>{!! trans('hyplast.table.id2') !!}</th>
                                <th>{!! trans('hyplast.table.storage') !!}</th>
                                <th>{!! trans('hyplast.table.user_storage') !!}</th>
                                <th>{!! trans('hyplast.table.pallets') !!}</th>
                                <th>{!! trans('hyplast.table.status') !!}</th>
                                <th>{!! trans('hyplast.table.actions') !!}</th>
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

        @include('scripts.datatables.datatables-transfers')



    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif
    <script type="text/javascript">

    </script>

@endsection


