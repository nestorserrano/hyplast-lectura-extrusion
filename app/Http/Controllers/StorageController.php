<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\Storage;
use App\Models\Product;
use App\Models\Machine;
use App\Models\Requisition;
use App\Models\Location;
use App\Models\Transfer;
use App\Models\TransferDetail;
use RealRashid\SweetAlert\Facades\Alert;
use DB;
use Carbon\Carbon;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use App\Helpers\Helper;
use App\Helpers\ZplPrinter;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\SchemaHelper;
use App\Traits\HasPermissionChecks;



class StorageController extends Controller
{
    use HasPermissionChecks;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->abortIfCannot('storages.view');

        $paginationEnabled = config('hyplast.enablePagination');
        if ($paginationEnabled) {
            $storages = Storage::paginate(config('hyplast.paginateListSize'));
        } else {
            $storages = Storage::all();
        }

        $storages = Storage::all();
        return view('storages.home', compact('storages'));
    }

    public function transport()
    {
        $this->abortIfCannot('storages.transport');

        $products = Product::where('status','=',false)->get();
        return view('storages.transport', compact('products'));
    }

    public function show(Storage $storage)
    {
       return view('storages.show', compact('storage'));
    }
    public function transfer()
    {
        $this->abortIfCannot('storages.transfer');

        $paginationEnabled = config('hyplast.enablePagination');
        if ($paginationEnabled) {
            $transfers = Transfer::paginate(config('hyplast.paginateListSize'));
        } else {
            $transfers = Transfer::all();
        }

        return view('storages.transfer', compact('transfers'));
    }

    public function store(Request $request)
    {

    }

    public function reqstorage($id)
    {
        $storage=DB::table('storages')
    	->join('products','storages.product_id','=','products.id')
        ->join('machines','storages.machine_id','=','machines.id')
    	->select('storages.id','storages.product_id','storages.user_production','storages.quantity','storages.machine_id','storages.requisition_id','storages.batch','products.code','products.name','products.dunnage_size','machines.location_id')
    	->where('storages.batch','=',$id)
        ->where('storages.transfer','=',false)
    	->first();
       return $storage;
    }

    public function register(Request $request)
    {
        $this->abortIfCannot('storages.create');

        // Validar que la requisición esté aprobada
        $requisition = Requisition::find($request->input('requisition'));
        if ($requisition && !$requisition->approved_by) {
            return response()->json([
                'success' => false,
                'message' => 'La orden de producción no está aprobada. No se puede registrar la producción.'
            ], 422);
        }

        $validator = Validator::make($request->all(),
        [
            'product'          => 'required',
            'quantity'         => 'required',
            'requisition'      => 'required',
            'machine'          => 'required',
            'storage'          => 'required',
            'batch'            => 'required',
            'pallet'           => 'required',
            'received'         => 'required',
            'userp'            => 'required',
        ],
        [
            'quantity.required'         => trans('hyplast.quantityRequired'),
            'product.required'          => trans('hyplast.productRequired'),
            'storage.required'          => trans('hyplast.storageRequired'),
            'machine.required'          => trans('hyplast.machineRequired'),
            'requisition.required'      => trans('hyplast.requisitionRequired'),
            'batch.required'            => trans('hyplast.batchRequired'),
            'pallet.required'           => trans('hyplast.palletRequired'),
            'received.required'         => trans('hyplast.receivedRequired'),
            'userp.required'            => trans('hyplast.userproductionRequired'),

        ]);


        if ($validator->fails()) {
            $message = "Error Validando los Campos, Verifique";
            Alert::error('Error',$message);
            return back()->withErrors($validator)->withInput();
        }

        $product=$request->get('product');
        $quantity=$request->get('quantity');
        $requisition=$request->get('requisition');
        $machine=$request->get('machine');
        $batch=$request->get('batch');
        $storage2=$request->get('storage');
        $location2=$request->get('location_id');
        $received=$request->get('received');
        $userp=$request->get('userp');


        DB::beginTransaction();

    	try {
            $transfer = Transfer::create([
                'date_storage' =>Carbon::now()->format('d-m-Y H:i'),
                'user_storage' => Auth::User()->id,
                'pallets' => count($product),
            ]);
            //$transfered = $transfer->id;
        }
        catch(ValidationException $e)
        {
            DB::rollback();
	        return Redirect::to('/storages/transport')
		        ->withErrors( $e->getErrors() )
		        ->withInput();
        }
        catch(\Exception $e)
        {
	        DB::rollback();
	        throw $e;
        }

        try
        {

    		$cont=0;
    		while ($cont < count($product))
    		{

    			$detalle = new TransferDetail();
    			$detalle->transfer_id=$transfer->id;
                $detalle->requisition_id=$requisition[$cont];
                $detalle->machine_id=$machine[$cont];
    			$detalle->product_id=$product[$cont];
                $detalle->batch=$batch[$cont];
                $detalle->quantity=$quantity[$cont];
                $detalle->location_id=$location2[$cont];
                $detalle->storage_id=$storage2[$cont];
                $detalle->received=$received[$cont];
                $detalle->pallet=$cont+1;
                $detalle->user_production=$userp[$cont];
    			$detalle->save();


                $storage = Storage::find($storage2[$cont]);
                $storage->date_storage = Carbon::now()->format('d-m-Y H:i');
                $storage->user_storage = Auth::User()->id;
                $storage->transfer = true;
                $storage->save();

    			$cont=$cont+1;
    		}

            $transfer->pallets = $cont;
            $transfer->save();
        }
        catch(ValidationException $e)
        {
            DB::rollback();
            $success = false;
            $message = "Ocurrió un error creando los detalles de la Transferencia, llame al Departamento de tecnología, no continue.";
            Alert::success('¡Lo sentimos!',$message);
            return Redirect::to('/storages/transport')
                ->withErrors( $e->getErrors() )
                ->withInput();
        }
        catch(\Exception $e)
        {
            DB::rollback();
            throw $e;
            $success = false;
            $message = "Ocurrió un error realizando el Traslado, verifique la información";
            Alert::success('¡Lo sentimos!',$message);
        }

   		DB::commit();


        $success = true;
        $message = "Recepción de Traslado Realizado correctamente";
        Alert::success('¡Felicidades!',$message);
        return back()->with('success', trans('hyplast.createSuccess'));
    }

    public function ticket($id)
    {
        Helper::printTransferTicket($id);
        return back();
    }

    public function printtransfer($id)
    {

        $transfer = Transfer::join("users","users.id","=","transfers.user_storage")
            ->selectraw("transfers.id,transfers.date_storage,CONCAT(users.first_name,' ',users.last_name) as userstore,transfers.status,transfers.pallets")
            ->where("transfers.id","=",$id)
            ->first();
        $transferdetails = TransferDetail::join("transfers","transfers.id","=","transfer_details.transfer_id")
            ->join("locations","locations.id","=","transfer_details.location_id")
            ->join("products","products.id","=","transfer_details.product_id")
            ->selectraw("transfers.id,locations.name as location,products.code,products.name as product,COUNT(transfer_details.pallet) as pallet,SUM(transfer_details.quantity) as quantity,SUM(transfer_details.received) as received")
            ->where("transfers.id","=",$id)
            ->groupBy("transfers.id","locations.name","products.code","products.name")
            ->get();
        $totals = TransferDetail::join("transfers","transfers.id","=","transfer_details.transfer_id")
            ->selectraw("COUNT(*) as pallets,SUM(transfer_details.quantity) as quantity,SUM(transfer_details.received) as received")
            ->where("transfers.id","=",$id)
            ->get();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $name = 'RECEPTION-' . $transfer->id . '.pdf';
        $view = \View::make('storages.print.transfer', compact('transfer','transferdetails','totals'))->render();
        $pdf = \PDF::loadHTML($view);
        return $pdf->stream($name);
    }

    public function printtransferdet($id)
    {

        $transfer = Transfer::join("users","users.id","=","transfers.user_storage")
            ->selectraw("transfers.id,transfers.date_storage,CONCAT(users.first_name,' ',users.last_name) as userstore,transfers.status,transfers.pallets")
            ->where("transfers.id","=",$id)
            ->first();
        $transferdetails = TransferDetail::join("transfers","transfers.id","=","transfer_details.transfer_id")
            ->join("locations","locations.id","=","transfer_details.location_id")
            ->join("products","products.id","=","transfer_details.product_id")
            ->selectraw("transfers.id,locations.name as location,products.code,products.name as product,transfer_details.pallet,transfer_details.quantity,transfer_details.received")
            ->where("transfers.id","=",$id)
            ->get();
        $totals = TransferDetail::join("transfers","transfers.id","=","transfer_details.transfer_id")
            ->selectraw("COUNT(*) as pallets,SUM(transfer_details.quantity) as quantity,SUM(transfer_details.received) as received")
            ->where("transfers.id","=",$id)
            ->get();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $name = 'REC-DETAIL-' . $transfer->id . '.pdf';
        $view = \View::make('storages.print.transfer', compact('transfer','transferdetails','totals'))->render();
        $pdf = \PDF::loadHTML($view);
        return $pdf->stream($name);
    }

    public function delete($id)
    {
        $delete = Storage::where('id', $id)->delete();
        // check data deleted or not
        if ($delete == 1) {
            $success = true;
            $message = "Producción eliminada Correctamente";
        } else {
            $success = true;
            $message = "Producción no Encontrada";
        }
        //  Return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function storageData(Request $request)
    {
        $schema = SchemaHelper::getSchema();
        $hoy = Carbon::today(); //Aquí se obtiene la fecha de hoy

        $data = Storage::join("{$schema}.ARTICULO as products","products.ARTICULO","=","storages.product_id")
        ->join("{$schema}.U_MAQUINAS as machines","machines.U_CODIGO","=","storages.machine_id")
        ->join("requisitions","requisitions.id","=","storages.requisition_id")
        ->join("users","users.id","=","storages.user_production")
        ->selectraw("storages.id,storages.requisition_id as requisition,machines.U_NOMBRE as machine,products.DESCRIPCION as product,storages.quantity as quantity,storages.net_weight as netweight,storages.total_weight as totalweight,CONCAT(users.first_name,' ',users.last_name) as userproduction,storages.transfer as status")
        ->where("products.TIPO", "=", "T")
        ->where("products.ACTIVO", "=", "S")
        ->whereDate("storages.created_at", "=", $hoy)
        ->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action0', function ($data) {
                if ($data->transfer) {
                    return '<a class="btn btn-sm btn-info btn-block disabled" href="storage-ticket/' . $data->id . '" data-toggle="tooltip" title="Ticket"><i class="fas fa-ticket-alt"></i> <span class="hidden-xs hidden-sm">' . trans("hyplast.buttons.ticket") . '</span></a>';
                } else {
                    return ButtonHelper::custom('storage-ticket/' . $data->id, 'btn-info', 'fa-ticket-alt', trans("hyplast.buttons.ticket"));
                }
            })
            ->rawColumns(['action0'])
            ->make(true);
    }

    public function storageDashboard(Request $request)
    {

        $hoy = Carbon::today(); //Aquí se obtiene la fecha de hoy
        $box = Storage::selectRaw("SUM(storages.quantity) as quantity")->whereDate("storages.created_at", "=", $hoy)->get();
        $naves = Storage::join("machines","machines.id","=","storages.machine_id")
                ->join("locations","locations.id","=","machines.location_id")
                ->selectraw("locations.name,SUM(storages.quantity) as quantity")
                ->whereDate("storages.created_at", "=", $hoy)
                ->groupBy("locations.name")
                ->get();
        $material = Storage::join("products","products.id","=","storages.product_id")
                    ->join("materials","materials.id","=","products.material_id")
                    ->selectraw("materials.name,SUM(storages.quantity) as quantity")
                    ->whereDate("storages.created_at", "=", $hoy)
                    ->groupBy("materials.name")
                    ->get();
        $kilo = Storage::selectRaw("SUM(storages.total_weight) as totalweight")->whereDate("storages.created_at", "=", $hoy)->get();
        $navesk = Storage::join("machines","machines.id","=","storages.machine_id")
                ->join("locations","locations.id","=","machines.location_id")
                ->selectraw("locations.name,SUM(storages.total_weight) as totalweight")
                ->whereDate("storages.created_at", "=", $hoy)
                ->groupBy("locations.name")
                ->get();
        $materialk = Storage::join("products","products.id","=","storages.product_id")
                ->join("materials","materials.id","=","products.material_id")
                ->selectraw("materials.name,SUM(storages.total_weight) as totalweight")
                ->whereDate("storages.created_at", "=", $hoy)
                ->groupBy("materials.name")
                ->get();
        $piso = Storage::selectRaw("SUM(storages.quantity) as quantity")->where("storages.transfer", "=", 0)->get();
        $pisok = Storage::selectRaw("SUM(storages.total_weight) as totalweight")->where("storages.transfer", "=", 0)->get();
        return response()->json([
            'box'       => $box,
            'naves'     => $naves,
            'material'  => $material,
            'kilo'      => $kilo,
            'navesk'    => $navesk,
            'materialk' => $materialk,
            'piso'      => $piso,
            'pisok'     => $pisok,

        ]);
    }

}

