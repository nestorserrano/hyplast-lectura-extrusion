@extends('adminlte::page')


@section('template_title')
  {!! trans('hyplast.showing-user', ['name' => $categorymachine->name]) !!}
@endsection

@section('content')

  <div class="container">
    <div class="row">
      <div class="col-lg-10 offset-lg-1">

        <div class="card">

          <div class="card-header text-white bg-success">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              {!! trans('hyplast.showing-machine-title', ['name' => $categorymachine->name]) !!}
              <div class="pull-right">
                <a href="{{ route('categorymachines') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                    {!! trans('hyplast.buttons.back-to-machines') !!}
                </a>
            </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row">

                  <div class="col">
                <h4 class="text-muted margin-top-sm-1 text-center text-left-tablet">
                  {{ $categorymachine->id }}
                </h4>
                <p class="text-center text-left-tablet">
                  <strong>
                    {{ $categorymachine->name }}
                  </strong>
                </p>
                <br />

              </div>
            </div>
            <br />
            <div class="clearfix"></div>
            <div class="border-bottom"></div>


            @if ($categorymachine->name)

            <div class="col-sm-5 col-6 text-larger">
              <strong>
                {{ trans('hyplast.labelname') }}
              </strong>
            </div>

            <div class="col-sm-7">
                {{ $categorymachine->name }}
              </div>

            <div class="clearfix"></div>
            <div class="border-bottom"></div>

            @endif


            @if ($categorymachine->created_at)

              <div class="col-sm-5 col-6 text-larger">
                <strong>
                  {{ trans('hyplast.labelCreatedAt') }}
                </strong>
              </div>

              <div class="col-sm-7">
                {{ $categorymachine->created_at }}
              </div>

              <div class="clearfix"></div>
              <div class="border-bottom"></div>

            @endif

            @if ($categorymachine->updated_at)

              <div class="col-sm-5 col-6 text-larger">
                <strong>
                  {{ trans('hyplast.labelUpdatedAt') }}
                </strong>
              </div>

              <div class="col-sm-7">
                {{ $categorymachine->updated_at }}
              </div>
            @endif
            <br />

            @if ($categorymachine->id)

                <div class="row align-items-end">
                    <div class="col">
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <a class="btn btn-sm btn-info btn-block" href="{{ URL::to('categorymachines/' . $categorymachine->id . '/edit') }}" data-toggle="tooltip" title="Editar">
                                {!! trans('hyplast.buttons.edit') !!}
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-center">
                            <button class="btn  btn-sm btn-danger btn-block" type="submit" onclick="deleteConfirmation({{ $categorymachine->id}})"> {!! trans('hyplast.buttons.delete') !!}</button>
                        </div>
                    </div>
                    <div class="col">

                    </div>
                </div>
            @endif
    </div>
</div>

      </div>
    </div>

  </div>
  </div>
@endsection

@section('footer_scripts')
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
                        url: "{{url('/categorymachines/delete')}}/" + id,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("Done!", results.message, "success");
                                window.location = "/categorymachines";
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
