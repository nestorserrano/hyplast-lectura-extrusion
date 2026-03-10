<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ArticuloFoto;
use App\Models\Category;
use App\Models\Material;
use App\Models\Color;
use App\Models\Aplication;
use App\Models\Diameter;
use App\Models\Capacity;
use App\Models\Inche;
use App\Models\Machine;
use App\Models\Location;
use App\Models\FamilyProduct;
use App\Models\SubProduct;
use App\Models\Article;
use App\Models\Supplie;
use App\Traits\CaptureIpTrait;
use App\Helpers\SchemaHelper;
use App\Helpers\ButtonHelper;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Validator;
use RealRashid\SweetAlert\Facades\Alert;
use File;
use Image;
use \Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$machines = Machine::all();
        //$articles = Article::select("ARTICULO","DESCRIPCION")->where("CLASIFICACION_1","<>","PT")->where("TIPO", "<>", "T")->where("ACTIVO","<>","N")->get();
        return View('products.home');
    }



    /**
     * Obtiene datos de productos para DataTables con filtros
     *
     * MAPEO DE CLASIFICACIONES SOFTLAND:
     * - GRUPO:     ARTICULO.CLASIFICACION_1 -> CLASIFICACION.AGRUPACION = 1
     * - PROCESO:   ARTICULO.CLASIFICACION_2 -> CLASIFICACION.AGRUPACION = 2
     * - FAMILIA:   ARTICULO.CLASIFICACION_3 -> CLASIFICACION.AGRUPACION = 3
     * - CATEGORIA: ARTICULO.CLASIFICACION_4 -> CLASIFICACION.AGRUPACION = 4
     * - MATERIAL:  ARTICULO.CLASIFICACION_5 -> CLASIFICACION.AGRUPACION = 5
     * - COLOR:     ARTICULO.CLASIFICACION_6 -> CLASIFICACION.AGRUPACION = 6
     *
     * @param Request $request Filtros: articulo, grupo, familia, categoria, color
     * @return JsonResponse DataTables JSON
     */
    public function ProductData(Request $request)
    {
        $schema = SchemaHelper::getSchema();

        try {
            $query = DB::connection('softland')
            ->table("{$schema}.ARTICULO as a")
            ->leftJoin("{$schema}.CLASIFICACION as grupo", function($join) {
                $join->on("grupo.CLASIFICACION", "=", "a.CLASIFICACION_1")
                     ->where("grupo.AGRUPACION", "=", "1");
            })
            ->leftJoin("{$schema}.CLASIFICACION as materials", function($join) {
                $join->on("materials.CLASIFICACION", "=", "a.CLASIFICACION_5")
                     ->where("materials.AGRUPACION", "=", "5");
            })
            ->leftJoin("{$schema}.CLASIFICACION as colors", function($join) {
                $join->on("colors.CLASIFICACION", "=", "a.CLASIFICACION_6")
                     ->where("colors.AGRUPACION", "=", "6");
            })
            ->leftJoin("{$schema}.CLASIFICACION as familyproducts", function($join) {
                $join->on("familyproducts.CLASIFICACION", "=", "a.CLASIFICACION_3")
                     ->where("familyproducts.AGRUPACION", "=", "3");
            })
            ->leftJoin("{$schema}.CLASIFICACION as categoria", function($join) {
                $join->on("categoria.CLASIFICACION", "=", "a.CLASIFICACION_4")
                     ->where("categoria.AGRUPACION", "=", "4");
            })
            ->leftJoin("{$schema}.ARTICULO_FOTO as foto", function($join) use ($schema) {
                $join->on("foto.ARTICULO", "=", "a.ARTICULO")
                     ->whereRaw("foto.PRIORIDAD = (SELECT MIN(PRIORIDAD) FROM {$schema}.ARTICULO_FOTO WHERE ARTICULO = a.ARTICULO)");
            })
            ->select(
                "a.ARTICULO as code",
                "a.DESCRIPCION as name",
                "grupo.DESCRIPCION as grupo",
                "familyproducts.DESCRIPCION as familia",
                "categoria.DESCRIPCION as categoria",
                "colors.DESCRIPCION as color",
                "materials.DESCRIPCION as material",
                "foto.FOTO as foto",
                DB::raw("(SELECT COUNT(*) FROM {$schema}.ARTICULO_FOTO WHERE ARTICULO = a.ARTICULO) as total_fotos")
            )
            ->whereIn("a.TIPO", ["T", "E"])
            ->where("a.ACTIVO", "=", "S");

            // Aplicar filtros
            if ($request->filled('articulo')) {
                $query->where('a.ARTICULO', 'like', '%' . $request->articulo . '%');
            }

            if ($request->filled('grupo')) {
                $query->where('a.CLASIFICACION_1', $request->grupo);
            }

            if ($request->filled('familia')) {
                $query->where('a.CLASIFICACION_3', $request->familia);
            }

            if ($request->filled('categoria')) {
                $query->where('a.CLASIFICACION_4', $request->categoria);
            }

            if ($request->filled('color')) {
                $query->where('a.CLASIFICACION_6', $request->color);
            }

            if ($request->filled('tipo')) {
                $query->where('a.TIPO', $request->tipo);
            }

            $data = $query->orderBy('a.TIPO', 'desc')  // T (Terminado) antes que E (SemiElaborado)
                          ->orderBy('a.ARTICULO', 'asc')
                          ->get();
        } catch (\Exception $e) {
            \Log::warning("Error al consultar productos en esquema {$schema}: " . $e->getMessage());
            $data = collect([]);
        }
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('imagen', function ($data) {
                if ($data->foto) {
                    $fotoBase64 = base64_encode($data->foto);
                    $fotoUrl = 'data:image/jpeg;base64,' . $fotoBase64;
                    $totalFotos = $data->total_fotos;
                    $masText = $totalFotos > 1 ? '<small class="text-muted">+' . ($totalFotos - 1) . ' más</small>' : '';

                    return '<div class="text-center">
                                <img src="' . $fotoUrl . '" alt="' . $data->name . '"
                                     style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                     class="img-thumbnail producto-imagen"
                                     data-code="' . $data->code . '"
                                     onclick="mostrarImagenesProducto(\'' . $data->code . '\')">
                                <br>' . $masText . '
                            </div>';
                } else {
                    return '<div class="text-center">
                                <img src="' . asset('images/no_image_available.png') . '"
                                     style="width: 60px; height: 60px; object-fit: cover;"
                                     class="img-thumbnail">
                                <br><small class="text-muted">Sin foto</small>
                            </div>';
                }
            })
            ->addColumn('action', function ($data) {
                // Botones inline con separación usando mr-1 (margin-right)
                $machine = '<button type="button" class="btn btn-sm btn-primary mr-1" onclick="window.modalMachine(\'' . $data->code . '\')" title="Máquinas">' . trans('hyplast.buttons.machine') . '</button>';
                $supplies = '<button type="button" class="btn btn-sm btn-warning mr-1" onclick="window.modalSupplies(\'' . $data->code . '\')" title="Insumos">' . trans('hyplast.buttons.supplies') . '</button>';
                $show = '<a href="products/' . $data->code . '" class="btn btn-success btn-sm mr-1" title="Ver">' . trans('hyplast.buttons.show') . '</a>';
                $edit = '<a href="products/' . $data->code . '/edit" class="btn btn-primary btn-sm" title="Editar">' . trans('hyplast.buttons.edit') . '</a>';

                return '<div class="text-nowrap">' . $machine . $supplies . $show . $edit . '</div>';
            })

            ->rawColumns(['imagen','action'])
            ->make(true);


    }


    /**
     * Show the form for creating a new resource.
     */
    /**
     * Función deshabilitada: Los productos se crean exclusivamente en Softland ERP
     * No se permite crear productos desde la aplicación web
     */
    public function create()
    {
        abort(403, 'Los productos deben crearse desde Softland ERP');

        /* CÓDIGO ORIGINAL COMENTADO - Productos se crean en Softland ERP
        $colors = Color::all();
        $materials = Material::all();
        $categories = Category::all();
        $aplications = Aplication::all();
        $capacities = Capacity::all();
        $diameters = Diameter::all();
        $inches = Inche::all();
        $subproducts = SubProduct::all();
        $familyproducts = FamilyProduct::all();

        return view('products.create', compact('colors','materials','categories','aplications','diameters','capacities','inches', 'subproducts', 'familyproducts'));
        */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {

        $codeCheck = ($request->input('code') !== '') && ($request->input('code') !== $product->code);
        $cartonCheck = Str::length($request->input('cartonsize') > 1);

        if ($codeCheck) {

            $validator = Validator::make($request->all(),
            [
                'code'                 => 'required|max:10|unique:products|alpha_dash',
                'barcode'              => 'unique:products|numeric|digits_between:0,13',
                'name'                 => 'required',
                'category_id'          => 'required',
                //'picture1'             => 'image|mimes:jpeg,png,jpg,gif',
                //'picture2'             => 'image|mimes:jpeg,png,jpg,gif',
                'subproduct_id'           => 'required',
                'familyproduct_id'        => 'required',

            ],
            [
                'code.unique'                => trans('hyplast.codeTaken'),
                'barcode.unique'             => trans('hyplast.barcodeTaken'),
                'code.required'              => trans('hyplast.codeRequired'),
                'barcode.numeric'            => trans('hyplast.barcodeNumeric'),
                'name.required'              => trans('hyplast.NameRequired'),
                'category_machine.required'  => trans('hyplast.Category_machineRequired'),
                //'picture1.image'             => trans('hyplast.type_image'),
                //'picture2.image'             => trans('hyplast.type_image'),
                'subproduct_id.required'        => trans('hyplast.subproductRequired'),
                'familyproduct_id.required'        => trans('hyplast.subproductRequired'),
                'barcode.unique'             => trans('hyplast.familyproductRequired'),
            ]);
        } else {
            $validator = Validator::make($request->all(),
            [
                'name'                 => 'required',
                'barcode'              => 'required',
                'category_id'          => 'required',
                //'picture1'              => 'image|mimes:jpeg,png,jpg,gif',
                //'picture2'              => 'image|mimes:jpeg,png,jpg,gif',
            ],
            [

                'name.required'              => trans('hyplast.NameRequired'),
                'category_id.required'       => trans('hyplast.Category_machineRequired'),
                //'picture1.image'             => trans('hyplast.type_image'),
               // 'picture2.image'             => trans('hyplast.type_image'),
            ]);
        }

        if ($validator->fails()) {
            $message = "Error Validando los Campos, Verifique";
            Alert::error('Error',$message);
            return back()->withErrors($validator)->withInput();
        }

        $product->name = strip_tags($request->input('name'));
        $product->color_id = strip_tags($request->input('color_id'));
        $product->material_id = strip_tags($request->input('material_id'));
        $product->category_id = strip_tags($request->input('category_id'));
        $product->aplication_id = strip_tags($request->input('aplication_id'));
        $product->capacity_id = strip_tags($request->input('capacity_id'));
        $product->diameter_id = strip_tags($request->input('diameter_id'));
        $product->inche_id = strip_tags($request->input('inche_id'));
        $product->design = strip_tags($request->input('design'));
        $product->hole = strip_tags($request->input('hole'));
        if($cartonCheck>1) {
            $product->cartonsize = strip_tags($request->input('cartonsize'));
        } else {
            $nombre = (strip_tags($request->input('length') . ' cm x ' . $request->input('width') . ' cm x ' . $request->input('height') . ' cm'));
            $product->cartonsize = $nombre;
        }
        $product->division = strip_tags($request->input('division'));
        $product->dunnage_size = strip_tags($request->input('dunnage_size'));
        $product->composition = strip_tags($request->input('composition'));
        $product->raw_material = strip_tags($request->input('raw_material'));
        $product->useful_life = strip_tags($request->input('useful_life'));
        $product->unit_weight = strip_tags($request->input('unit_weight'));
        $product->net_weight = strip_tags($request->input('net_weight'));
        $product->gross_weight = strip_tags($request->input('gross_weight'));
        $product->barcode = strip_tags($request->input('barcode'));
        $product->caliber_lamination = strip_tags($request->input('caliber_lamination'));
        $product->width_lamination = strip_tags($request->input('width_lamination'));
        $product->length = strip_tags($request->input('length'));
        $product->height = strip_tags($request->input('height'));
        $product->width = strip_tags($request->input('width'));
        $product->package_units = strip_tags($request->input('package_units'));
        $product->package_box = strip_tags($request->input('package_box'));
        $product->box_units = strip_tags($request->input('box_units'));
        $product->box_litter = strip_tags($request->input('box_litter'));
        $product->platform_litter = strip_tags($request->input('platform_litter'));
        $product->laminate = strip_tags($request->input('laminate'));
        $product->location_id = 1;
        $product->subproduct_id = strip_tags($request->input('subproduct_id'));
        $product->familyproduct_id = strip_tags($request->input('familyproduct_id'));
        $product->time_production = strip_tags($request->input('time_production'));
        $product->status = 1;

        $currentProduct = strip_tags($request->input('code'));

        if ($request->File('picture1')) {
            $imagen1 = $request->file('picture1');
            $filename1 = $currentProduct . "." . $request->file('picture1')->getClientOriginalExtension();
            $save_path1 = storage_path()."/products/" . $currentProduct;
            File::makeDirectory($save_path1, $mode = 0755, true, true);
            $path1 = $save_path1.'/'.$filename1;
            $public_path1 = '/products/'.$currentProduct.'/'.$filename1;
            Image::make($imagen1)->resize(300,300)->save($save_path1.'/'.$filename1);
            $product->picture1 = $public_path1;
        }
        else {
            $product->picture1 = 'Sin Imágen';
        }

        if ($request->File('picture2')) {
            $imagen2 = $request->file('picture2');
            $filename2 = '2.'. $request->file('picture2')->getClientOriginalExtension();
            $save_path2 = storage_path()."/products/" . $currentProduct;
            File::makeDirectory($save_path2, $mode = 0755, true, true);
            $path2 = $save_path2.'/'.$filename2;
            $public_path2 = '/products/'.$currentProduct.'/'.$filename2;
            Image::make($imagen2)->resize(300,300)->save($save_path2.'/'.$filename2);
            $product->picture2 = $public_path2;
        }
        else {
            $product->picture2 = 'Sin Imágen';
        }


        if ($codeCheck) {
            $product->code = strip_tags($request->input('code'));
        };

        $product->save();

        $success = true;
        $message = "Producto creado Correctamente";


        Alert::success('¡Felicidades!',$message);
        return back()->with('success', trans('hyplast.createSuccess'));



    }

    /**
     * Display the specified resource.
     */
    public function show($codigo)
    {
        // Buscar producto por código (ARTICULO) en Softland
        $product = Product::where('ARTICULO', $codigo)->first();

        if (!$product) {
            Alert::error('Error', 'Producto no encontrado');
            return redirect('products');
        }

        // Obtener descripciones de clasificaciones
        $clasificaciones = [];

        // Agrupación 1: Grupo
        if ($product->CLASIFICACION_1) {
            $clasificaciones['grupo'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_1)
                ->where('AGRUPACION', 1)
                ->first();
        }

        // Agrupación 2: Proceso
        if ($product->CLASIFICACION_2) {
            $clasificaciones['proceso'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_2)
                ->where('AGRUPACION', 2)
                ->first();
        }

        // Agrupación 3: Familia
        if ($product->CLASIFICACION_3) {
            $clasificaciones['familia'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_3)
                ->where('AGRUPACION', 3)
                ->first();
        }

        // Agrupación 4: Categoría
        if ($product->CLASIFICACION_4) {
            $clasificaciones['categoria'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_4)
                ->where('AGRUPACION', 4)
                ->first();
        }

        // Agrupación 5: Material
        if ($product->CLASIFICACION_5) {
            $clasificaciones['material'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_5)
                ->where('AGRUPACION', 5)
                ->first();
        }

        // Agrupación 6: Color
        if ($product->CLASIFICACION_6) {
            $clasificaciones['color'] = \App\Models\Clasification::where('CLASIFICACION', $product->CLASIFICACION_6)
                ->where('AGRUPACION', 6)
                ->first();
        }

        $barra = new DNS1D();
        return view('products.show', compact('product', 'barra', 'clasificaciones'));
    }

    /**
     * Obtener todas las imágenes de un producto
     */
    public function getImagenes($codigo)
    {
        $schema = SchemaHelper::getSchema();

        $fotos = DB::connection('softland')
            ->table("{$schema}.ARTICULO_FOTO")
            ->where('ARTICULO', $codigo)
            ->orderBy('PRIORIDAD', 'asc')
            ->get();

        $imagenesArray = [];
        foreach ($fotos as $foto) {
            if ($foto->FOTO) {
                $imagenesArray[] = [
                    'secuencia' => $foto->SECUENCIA,
                    'prioridad' => $foto->PRIORIDAD,
                    'url' => 'data:image/jpeg;base64,' . base64_encode($foto->FOTO)
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total' => count($imagenesArray),
            'imagenes' => $imagenesArray
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($codigo)
    {
        // Buscar producto por código (ARTICULO) en Softland
        $product = Product::where('ARTICULO', $codigo)->first();

        if (!$product) {
            Alert::error('Error', 'Producto no encontrado');
            return redirect('products');
        }

        $colors = Color::all();
        $materials = Material::all();
        $categories = Category::all();
        $aplications = Aplication::all();
        $capacities = Capacity::all();
        $diameters = Diameter::all();
        $inches = Inche::all();
        $subproducts = SubProduct::all();
        $familyproducts = FamilyProduct::all();


        $data =
        [
            'product'       => $product,
            'colors'        => $colors,
            'materials'     => $materials,
            'categories'    => $categories,
            'aplications'   => $aplications,
            'diameters'     => $diameters,
            'capacities'    => $capacities,
            'inches'        => $inches,
            'subproducts'    => $subproducts,
            'familyproducts' => $familyproducts,


        ];

        return view('products.edit')->with($data);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $codigo)
    {
        // Buscar producto por código (ARTICULO) en Softland
        $product = Product::where('ARTICULO', $codigo)->first();

        if (!$product) {
            Alert::error('Error', 'Producto no encontrado');
            return redirect('products');
        }

        $codeCheck = ($request->input('code') !== '') && ($request->input('code') !== $product->code);
        $cartonCheck = Str::length($request->input('cartonsize') > 1);


        $product->name = strip_tags($request->input('name'));
        $product->color_id = strip_tags($request->input('color_id'));
        $product->material_id = strip_tags($request->input('material_id'));
        $product->category_id = strip_tags($request->input('category_id'));
        $product->aplication_id = strip_tags($request->input('aplication_id'));
        $product->subproduct_id = strip_tags($request->input('subproduct_id'));
        $product->familyproduct_id = strip_tags($request->input('familyproduct_id'));
        $product->time_production = strip_tags($request->input('time_production'));
        $product->aplication_id = strip_tags($request->input('aplication_id'));
        $product->capacity_id = strip_tags($request->input('capacity_id'));
        $product->diameter_id = strip_tags($request->input('diameter_id'));
        $product->inche_id = strip_tags($request->input('inche_id'));
        $product->design = strip_tags($request->input('design'));
        $product->hole = strip_tags($request->input('hole'));

        if($cartonCheck>1) {
            $product->cartonsize = strip_tags($request->input('cartonsize'));
        } else {
            $nombre = (strip_tags($request->input('length') . ' cm x ' . $request->input('width') . ' cm x ' . $request->input('height') . ' cm'));
            $product->cartonsize = $nombre;
        }
        $product->division = strip_tags($request->input('division'));
        $product->dunnage_size = strip_tags($request->input('dunnage_size'));
        $product->composition = strip_tags($request->input('composition'));
        $product->raw_material = strip_tags($request->input('raw_material'));
        $product->useful_life = strip_tags($request->input('useful_life'));
        $product->unit_weight = strip_tags($request->input('unit_weight'));
        $product->net_weight = strip_tags($request->input('net_weight'));
        $product->gross_weight = strip_tags($request->input('gross_weight'));
        $product->barcode = strip_tags($request->input('barcode'));
        $product->caliber_lamination = strip_tags($request->input('caliber_lamination'));
        $product->width_lamination = strip_tags($request->input('width_lamination'));
        $product->length = strip_tags($request->input('length'));
        $product->height = strip_tags($request->input('height'));
        $product->width = strip_tags($request->input('width'));
        $product->package_units = strip_tags($request->input('package_units'));
        $product->package_box = strip_tags($request->input('package_box'));
        $product->box_units = strip_tags($request->input('box_units'));
        $product->box_litter = strip_tags($request->input('box_litter'));
        $product->platform_litter = strip_tags($request->input('platform_litter'));
        $product->laminate = strip_tags($request->input('laminate'));
        $product->location_id = 1;
        $product->status = 1;

        $currentProduct = strip_tags($request->input('code'));

        if ($request->File('picture1')) {
            $imagen1 = $request->file('picture1');
            $filename1 = $currentProduct . "." . $request->file('picture1')->getClientOriginalExtension();
            $save_path1 = storage_path()."/products/" . $currentProduct;
            $path1 = $save_path1.'/'.$filename1;
            $public_path1 = '/products/'.$currentProduct.'/'.$filename1;

            if(!File::isDirectory($save_path1)){
                File::makeDirectory($save_path1,  $mode = 0755, true, true);
            };
            if (@getimagesize($path1))
            {
                unlink($path1);
                Image::make($request->file('picture1'))->resize(300,300)->save($save_path1 . '/' . $filename1);
            }
            else
            {
                Image::make($request->file('picture1'))->resize(300,300)->save($save_path1 . '/' . $filename1);
            };

            $product->picture1 = $public_path1;
        }
        if ($request->File('picture2')) {
            $imagen2 = $request->file('picture2');
            $filename2 = '2.'. $request->file('picture2')->getClientOriginalExtension();
            $save_path2 = storage_path()."/products/" . $currentProduct;
            $path2 = $save_path2.'/'.$filename2;
            $public_path2 = '/products/'.$currentProduct.'/'.$filename2;

            if(!File::isDirectory($save_path2)){
                File::makeDirectory($save_path2,  $mode = 0755, true, true);
            };

            if (@getimagesize($path2))
            {
                unlink($path2);
                Image::make($request->file('picture2'))->resize(300,300)->save($save_path2 . '/' . $filename2);
            }
            else
            {
                Image::make($request->file('picture2'))->resize(300,300)->save($save_path2 . '/' . $filename2);
            };

            $product->picture2 = $public_path2;
        }

        if ($codeCheck) {
            $product->code = strip_tags($request->input('code'));
        };
       // dd($product);
        $product->save();

        $success = true;
        $message = "Producto actualizado Correctamente";


        Alert::success('¡Felicidades!',$message);
        return back()->with('success', trans('hyplast.updateSuccess'));



    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($codigo)
    {
        // Buscar producto por código (ARTICULO) en Softland
        $product = Product::where('ARTICULO', $codigo)->first();

        if (!$product) {
            Alert::error('Error', 'Producto no encontrado');
            return redirect('products');
        }

        $product->save();
        $product->delete();

        return redirect('products')->with('success', trans('hyplast.deleteSuccess'));
    }

    public function delete($id)
    {
        $delete = Product::where('id', $id)->delete();
        // check data deleted or not
        if ($delete == 1) {
            $success = true;
            $message = "Producto eliminado Correctamente";
        } else {
            $success = true;
            $message = "Producto no Encontrado";
        }
        //  Return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('machine_search_box');
        $searchRules = [
            'machine_search_box' => 'required|string|max:100',
        ];
        $searchMessages = [
            'machine_search_box.required' => 'El término de la Búsqueda es Requerido',
            'machine_search_box.string'   => 'La búsqueda posee términos inválidos',
            'machine_search_box.max'      => 'EL término de la búsqueda tiene demasiados caracteres - Solo se permiten menos de 100 caracteres',
        ];

        $validator = Validator::make($request->all(), $searchRules, $searchMessages);

        if ($validator->fails()) {
            return response()->json([
                json_encode($validator),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $results = Product::join("colors","colors.id","=","products.color_id")
                            ->join("materials","materials.id","=","products.material_id")
                            ->join("subproducts","subproducts.id","=","products.subproduct_id")
                            ->join("familyproducts","familyproducts.id","=","products.familyproduct_id")
                            ->select("products.id", "products.code","products.name","colors.name as color", "materials.name as material","subproducts.name", "familyproducts.name","products.created_at","products.updated_at")
                            ->Where('products.id', 'like', '%'.$searchTerm.'%')
                            ->orWhere('products.code', 'like', '%'.$searchTerm.'%')
                            ->orWhere('products.name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('colors.name', 'like', '%'.$searchTerm.'%')
                            ->orWhere('materials.name', 'like', '%'.$searchTerm.'%')->get();

        return response()->json([
            json_encode($results),
        ], Response::HTTP_OK);
    }


    /**
     * Obtener máquinas asociadas a un producto desde Softland
     * Usa tablas: U_MAQUINAS_ARTICULOS (pivot) y U_MAQUINAS
     *
     * @param string $id Código del artículo (varchar)
     * @return JsonResponse
     */
    public function productsmachines($id)
    {
        $schema = SchemaHelper::getSchema();

        try {
            // Consultar máquinas desde Softland usando U_MAQUINAS_ARTICULOS y U_MAQUINAS
            $machines = DB::connection('softland')
                ->table("{$schema}.U_MAQUINAS_ARTICULOS as uma")
                ->join("{$schema}.U_MAQUINAS as um", 'uma.U_CODIGO', '=', 'um.U_CODIGO')
                ->select(
                    'um.U_CODIGO as code',
                    'um.U_DESCRIP as name',
                    'um.U_CATEGORIA_MAQ as category_machine_id'
                )
                ->where('uma.ARTICULO', $id)
                ->get();

            \Log::info("Máquinas encontradas para producto {$id}: " . $machines->count());

            // Construir HTML de la tabla
            $html = '';
            if ($machines->count() > 0) {
                foreach ($machines as $machine) {
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . htmlspecialchars(trim($machine->code)) . '</td>';
                    $html .= '<td>' . htmlspecialchars(trim($machine->name)) . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html = '<tr><td colspan="2" class="text-center">No hay máquinas asociadas a este producto</td></tr>';
            }

            // Retornar como respuesta JSON con el HTML
            return response()->json(['tabla' => $html]);

        } catch (\Exception $e) {
            \Log::error("Error al obtener máquinas para producto {$id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json(['tabla' => '<tr><td colspan="2" class="text-center text-danger">Error al cargar máquinas</td></tr>'], 500);
        }
    }

    public function machinesproducts($id)
    {


        $machine = Machine::with('products')->findOrFail($id);
        $results = $machine;
        return $results;
    }

    public function autocomplete(Request $request)
    {
        $search = $request->search ?? $request->q; // Soportar tanto 'search' como 'q'
        $tipo = $request->tipo;
        $grupo = $request->grupo;
        $proceso = $request->proceso;

        $query = Article::orderby('ARTICULO','asc')
                        ->select('ARTICULO','DESCRIPCION', 'TIPO')
                        ->where("CLASIFICACION_1","<>","PT")
                        ->where("ACTIVO","<>","N");

        // Filtrar por tipo si se especifica
        if (!empty($tipo)) {
            $query->where("TIPO", "=", $tipo);
        } else {
            // Si no hay tipo, excluir productos terminados por defecto
            $query->where("TIPO", "<>", "T");
        }

        // Filtrar por grupo (CLASIFICACION_1)
        if (!empty($grupo)) {
            $query->where("CLASIFICACION_1", "=", $grupo);
        }

        // Filtrar por proceso (CLASIFICACION_2)
        if (!empty($proceso)) {
            $query->where("CLASIFICACION_2", "=", $proceso);
        }

        // Filtrar por búsqueda
        if(!empty($search)){
            $query->where(function($q) use ($search) {
                $q->where('DESCRIPCION', 'like', '%' . $search . '%')
                  ->orWhere('ARTICULO', 'like', '%' . $search . '%');
            });
        }

        $articles = $query->limit(100)->get();

        $response = array();
        foreach($articles as $article){
           $response[] = array(
                "id" => $article->ARTICULO,
                "text" => $article->ARTICULO . " - " . $article->DESCRIPCION
           );
        }
        return response()->json($response);

    }

    /**
     * Obtener clasificaciones por agrupación
     *
     * @param int $agrupacion Número de agrupación (1-6)
     * @return JsonResponse
     */
    public function getClasificaciones($agrupacion)
    {
        try {
            $schema = SchemaHelper::getSchema();

            $clasificaciones = DB::connection('softland')
                ->table("{$schema}.CLASIFICACION")
                ->select('CLASIFICACION', 'DESCRIPCION')
                ->where('AGRUPACION', $agrupacion)
                ->orderBy('DESCRIPCION', 'asc')
                ->get();

            return response()->json($clasificaciones);

        } catch (\Exception $e) {
            \Log::error('Error al obtener clasificaciones: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar clasificaciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos de productos activos
     *
     * @return JsonResponse
     */
    public function getTiposActivos()
    {
        try {
            $tipos = DB::table('product_types')
                ->select('code', 'name', 'description')
                ->where('active', 1)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($tipos);

        } catch (\Exception $e) {
            \Log::error('Error al obtener tipos de productos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar tipos de productos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener insumos asociados a un producto
     *
     * @param string $id Código del artículo (varchar)
     * @return JsonResponse
     */
    public function productssupplies($id)
    {
        try {
            $schema = SchemaHelper::getSchema();

            // Buscar producto por código de artículo (ARTICULO es varchar)
            $product = Product::where('ARTICULO', $id)->first();

            if (!$product) {
                return response()->json([
                    'tabla' => '<tr><td colspan="5" class="text-center text-danger">Producto no encontrado</td></tr>'
                ], 404);
            }

            // Obtener IDs de suministros desde tabla local
            $localSupplies = DB::table('products_supplies')
                ->where('product_id', $id)
                ->select('id', 'supplie_id', 'quantity')
                ->get();

            if ($localSupplies->isEmpty()) {
                return response()->json([
                    'tabla' => '<tr><td colspan="5" class="text-center">No hay insumos asociados a este producto</td></tr>'
                ]);
            }

            // Obtener detalles de artículos desde Softland
            $supplieIds = $localSupplies->pluck('supplie_id')->toArray();

            $articulos = DB::connection('softland')
                ->table("{$schema}.ARTICULO")
                ->whereIn('ARTICULO', $supplieIds)
                ->select('ARTICULO', 'DESCRIPCION', 'UNIDAD_ALMACEN')
                ->get()
                ->keyBy('ARTICULO');

            // Construir HTML de la tabla
            $html = '';
            foreach ($localSupplies as $item) {
                $articulo = $articulos->get($item->supplie_id);

                $html .= '<tr>';
                $html .= '<td class="text-center">' . htmlspecialchars($item->supplie_id) . '</td>';
                $html .= '<td>' . htmlspecialchars($articulo ? $articulo->DESCRIPCION : 'N/A') . '</td>';
                $html .= '<td class="text-center">' . htmlspecialchars($articulo ? $articulo->UNIDAD_ALMACEN : 'N/A') . '</td>';
                $html .= '<td class="text-center">' . number_format((float)$item->quantity, 2) . '</td>';
                $html .= '<td class="text-center">';
                $html .= '<button class="btn btn-sm btn-danger" type="button" onclick="deleteattach(' . $item->id . ')" title="Eliminar">Eliminar</button>';
                $html .= '</td>';
                $html .= '</tr>';
            }

            return response()->json(['tabla' => $html]);

        } catch (\Exception $e) {
            \Log::error('Error en productssupplies: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'tabla' => '<tr><td colspan="5" class="text-center text-danger">Error al cargar insumos</td></tr>'
            ], 500);
        }
    }

    public function attachsupplie($id, $product, $quantity)
    {
        DB::beginTransaction();

        try {
            $schema = SchemaHelper::getSchema();

            // Verificar que el artículo (suministro) exista en Softland
            $article = DB::connection('softland')
                ->table("{$schema}.ARTICULO")
                ->where('ARTICULO', $product)
                ->first();

            if (!$article) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'El artículo suministro no existe'
                ]);
            }

            // Verificar que no esté duplicado
            $exists = DB::table('products_supplies')
                ->where('product_id', $id)
                ->where('supplie_id', $product)
                ->exists();

            if ($exists) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Este suministro ya está asociado al producto'
                ]);
            }

            // Insertar en products_supplies
            DB::table('products_supplies')->insert([
                'product_id' => $id,
                'supplie_id' => $product,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Insumo agregado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en attachsupplie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar insumo: ' . $e->getMessage()
            ]);
        }
    }

    public function detachsupplie($recordId)
    {
        DB::beginTransaction();

        try {
            // Eliminar de products_supplies usando solo el ID del registro
            $deleted = DB::table('products_supplies')
                ->where('id', $recordId)
                ->delete();

            if (!$deleted) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el registro'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Insumo eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en detachsupplie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar insumo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function savequantity($id, $product, $quantity)
    {
        DB::beginTransaction();

        try {
            // Actualizar quantity en products_supplies
            $updated = DB::table('products_supplies')
                ->where('id', $id)
                ->where('product_id', $product)
                ->update([
                    'quantity' => $quantity,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el registro'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error en savequantity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cantidad: ' . $e->getMessage()
            ]);
        }
    }
    public function categoryproducts($id)
    {
        $products = Product::where('category_id', $id)->get();
        return response()->json($products);
    }

    /**
     * Obtener grupos para filtros
     * GRUPO: CLASIFICACION.AGRUPACION = 1
     * Se relaciona con ARTICULO.CLASIFICACION_1
     * @return JsonResponse
     */
    public function getGrupos()
    {
        $schema = SchemaHelper::getSchema();
        try {
            $grupos = DB::connection('softland')
                ->table("{$schema}.CLASIFICACION")
                ->where('AGRUPACION', '=', '1')
                ->orderBy('DESCRIPCION')
                ->get(['CLASIFICACION as value', 'DESCRIPCION as text']);

            return response()->json($grupos);
        } catch (\Exception $e) {
            \Log::error("Error al obtener grupos: " . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Obtener familias para filtros
     * FAMILIA: CLASIFICACION.AGRUPACION = 3
     * Se relaciona con ARTICULO.CLASIFICACION_3
     * @return JsonResponse
     */
    public function getFamilias()
    {
        $schema = SchemaHelper::getSchema();
        try {
            $familias = DB::connection('softland')
                ->table("{$schema}.CLASIFICACION")
                ->where('AGRUPACION', '=', '3')
                ->orderBy('DESCRIPCION')
                ->get(['CLASIFICACION as value', 'DESCRIPCION as text']);

            return response()->json($familias);
        } catch (\Exception $e) {
            \Log::error("Error al obtener familias: " . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Obtener categorías para filtros
     * CATEGORIA: CLASIFICACION.AGRUPACION = 4
     * Se relaciona con ARTICULO.CLASIFICACION_4
     * @return JsonResponse
     */
    public function getCategorias()
    {
        $schema = SchemaHelper::getSchema();
        try {
            $categorias = DB::connection('softland')
                ->table("{$schema}.CLASIFICACION")
                ->where('AGRUPACION', '=', '4')
                ->orderBy('DESCRIPCION')
                ->get(['CLASIFICACION as value', 'DESCRIPCION as text']);

            return response()->json($categorias);
        } catch (\Exception $e) {
            \Log::error("Error al obtener categorías: " . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Obtener colores para filtros
     * COLOR: CLASIFICACION.AGRUPACION = 6
     * Se relaciona con ARTICULO.CLASIFICACION_6
     * @return JsonResponse
     */
    public function getColores()
    {
        $schema = SchemaHelper::getSchema();
        try {
            $colores = DB::connection('softland')
                ->table("{$schema}.CLASIFICACION")
                ->where('AGRUPACION', '=', '6')
                ->orderBy('DESCRIPCION')
                ->get(['CLASIFICACION as value', 'DESCRIPCION as text']);

            return response()->json($colores);
        } catch (\Exception $e) {
            \Log::error("Error al obtener colores: " . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Obtener tipos de artículos activos
     */
    public function getProductTypes()
    {
        try {
            $types = \App\Models\ProductType::where('active', true)
                ->orderBy('name')
                ->get(['code as value', 'name as text', 'description']);

            return response()->json($types);
        } catch (\Exception $e) {
            \Log::error("Error al obtener tipos de productos: " . $e->getMessage());
            return response()->json([]);
        }
    }
}
