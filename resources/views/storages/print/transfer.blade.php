<head>
    <title>
        {!! trans('hyplast.showing-requisition', ['id' => $transfer->id]) !!}
    </title>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: Arial;
        }


        body {
            margin: 4cm 2cm 2cm;
        }

        header {
            position: fixed;
            top: 1cm;
            left: 2cm;
            right: 0cm;
            height: 2cm;
            color: rgb(0, 0, 0);
            text-align: center;
            line-height: 5px;

        }

        footer {
            position: fixed;
            bottom: 2.5cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            text-align: center;
            line-height: 25px;
        }

        tr:nth-child(even) {
            background-color: #bbb9b9e5;
        }

        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<header>

    <div class="table-responsive">
        <table style="border: 1px solid black; width:90%;  border-collapse: collapse;"  class="table">
            <tr style="border: 1px solid black;">
                <td style="border: 1px solid black; width:20%;" align='center'> <img style="width: 120px; align:center;" src="images/logo250x133.png" alt="Logo Hyplast"></td>
                <td style="border: 1px solid black; width:50%;" align='center' NOWRAP>
                    <h3>DEPARTAMENTO DE ALMACEN</h3>
                    <h4>LISTA DE RECEPCION</h4>
                </td>
                <td style="border: 1px solid black; width:20%;">
                    <p>Fecha: {{\Carbon\Carbon::now()->format('d - m - Y')}}</p>
                    <p><H2># REC-{{strval(str_pad($transfer->id, 5, "0", STR_PAD_LEFT))}}</H2></p>
                </td>
            </tr>
        </table>
    </div>
</header>
<main>
    <div class="container">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 align-self-center">
                        <strong>{!! Form::label('reception', trans('hyplast.store_label_reception'), array('class' => 'col-md-3 control-label')); !!}</strong>
                        {{strval(str_pad($transfer->id, 5, "0", STR_PAD_LEFT))}}
                    </div>
                    <div class="col-sm-6 align-self-center">
                        <strong>{!! Form::label('pallets', trans('hyplast.store_label_pallets'), array('class' => 'col-md-3 control-label')); !!}</strong>
                        {{ $transfer->pallets}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 align-self-center">
                        <strong>{!! Form::label('date_storage', trans('hyplast.store_label_date_store'), array('class' => 'col-md-4 control-label')); !!}</strong>
                        {{$transfer->date_storage->format('d-m-Y h:i A')}}
                    </div>
                    <div class="col-sm-6 align-self-center">
                        <strong>{!! Form::label('userstore', trans('hyplast.store_label_userstore'), array('class' => 'col-md-4 control-label')); !!}</strong>
                        {{$transfer->userstore }}
                    </div>
                </div>
            </div>
            <br>
            <div class="table-responsive">
                <table style="border: 1px solid black;  border-collapse: collapse; width:100%;"  class="table" >

                    <thead class="thead">
                        <tr style="border: 1px solid black; ">
                            <th style="border: 1px solid black;" align='center'>
                                {!! Form::label('line', 'Línea'); !!}
                            </th>
                            <th style="border: 1px solid black;" align='center'>
                                {!! Form::label('location', 'Viene de'); !!}
                            </th>
                            <th style="border: 1px solid black;" align='center'>
                                {!! Form::label('code', 'Código'); !!}
                            </th>
                            <th style="border: 1px solid black;" align='left'>
                                {!! Form::label('product', 'Producto'); !!}
                            </th>
                            <th style="border: 1px solid black;" align='center'>
                                {!! Form::label('pallet', 'Paletas'); !!}
                            </th>
                            <th style="border: 1px solid black;" align='center'>
                                {!! Form::label('received', 'Recibido'); !!}
                            </th>


                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 0
                        @endphp
                        @foreach($transferdetails as $transferdetail)
                            <tr style="border: 1px solid black;">
                                <td style="border: 1px solid black; width:10%;" align='center'>{{ $i = $i + 1 }}</td>
                                <td style="border: 1px solid black; width:10%;" align='center'>{{ $transferdetail->location}} </td>
                                <td style="border: 1px solid black; width:20%;" align='left'>{{ $transferdetail->code}}</td>
                                <td style="border: 1px solid black; width:40%;" align='left'>{{ $transferdetail->product}}</td>
                                <td style="border: 1px solid black; width:10%;" align='center'>{{ $transferdetail->pallet }}</td>
                                <td style="border: 1px solid black; width:10%;" align='center'>{{ $transferdetail->received}} </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="">
                            <td colspan="4" style="width:10%; font-weight-bold;" align='right'><strong>Totales-></strong></td>
                            <td style="border: 1px solid black; width:10%; font-weight-bold;" align='center'><strong>{{ $totals[0]->pallets }}</strong></td>
                            <td style="border: 1px solid black; width:10%; font-weight-bold;" align='center'><strong>{{ $totals[0]->received }}</strong></td>
                        </tr>
                    </tfoot>

                </table>
            </div>


        </div>
    </div>






    </main>

    <footer>
        <div style="align-items: center; text-align: center; justify-content: center;">
            <H4>Firma y Nombre de quien recibe: _____________________________________ </H4>
        </div>

        <script type="text/php">
            if (isset($pdf)) {
                $x = 480;
                $y = 740;
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                $font = null;
                $size = 12;
                $color = array(0,0,0);
                $word_space = 0.0;  //  default
                $char_space = 0.0;  //  default
                $angle = 0.0;   //  default
                $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            }
        </script>
    </footer>
    </body>

    </html>




