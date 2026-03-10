@extends('adminlte::page')

@section('template_title')
    {!! trans('hyplast.editing-machine', ['name' => $categorymachine->name]) !!}
@endsection

@section('template_linked_css')
@endsection

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('hyplast.editing-machine', ['name' => $categorymachine->name]) !!}
                            <div class="pull-right">
                                <a href="{{ route('categorymachines') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="top" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                                <a href="{{ url('/categorymachines/' . $categorymachine->id) }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machine') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {!! Form::open(array('route' => ['categorymachines.update', $categorymachine->id], 'method' => 'PUT', 'role' => 'form', 'class' => 'needs-validation')) !!}
                        {!! csrf_field() !!}


                            <div class="form-group has-feedback row {{ $errors->has('name') ? ' has-error ' : '' }}">
                                {!! Form::label('name', trans('forms.create_machine_label_name'), array('class' => 'col-md-3 control-label')); !!}
                                <div class="col-md-9">
                                    <div class="input-group">
                                        {!! Form::text('name', $categorymachine->name, array('id' => 'name', 'class' => 'form-control', 'placeholder' => trans('forms.create_machine_ph_name'))) !!}
                                        <div class="input-group-append">
                                            <label class="input-group-text" for="name">
                                                <i class="fa fa-fw {{ trans('forms.create_machine_icon_name') }}" aria-hidden="true"></i>
                                            </label>
                                        </div>
                                    </div>
                                    @if($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                                <div class="col-12 col-sm-6 mb-2">
                                </div>
                                <div class="col-12 col-sm-6">
                                    {!! Form::button(trans('forms.save-changes'), array('class' => 'btn btn-success btn-block margin-bottom-1 mt-3 mb-2 btn-save','type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#confirmSave', 'data-title' => trans('modals.edit_machine__modal_text_confirm_title'), 'data-message' => trans('modals.edit_machine__modal_text_confirm_message'))) !!}
                                    @include('modals.modal-save')
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')

@endsection
