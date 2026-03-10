<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Product extends SoftlandModel
{
    use HasFactory;

    // MIGRATED: Usa esquema dinámico vía SoftlandModel
    // La tabla se ajustará automáticamente según la empresa: {CONJUNTO}.ARTICULO
    protected $table = 'ARTICULO';
    protected $primaryKey = 'ARTICULO'; // String PK
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Softland no usa created_at/updated_at

    protected $guarded = [
        'ARTICULO',
    ];
    protected $fillable = [
        'ARTICULO',
        'DESCRIPCION',
        'CLASIFICACION_1', // grupo_id
        'CLASIFICACION_2', // proceso_id
        'CLASIFICACION_3', // familia_id
        'CLASIFICACION_4', // category_id
        'CLASIFICACION_5', // material_id
        'CLASIFICACION_6', // color_id
        'CODIGO_BARRAS_VENT', // barcode
        'ACTIVO', // status
        'PESO_NETO', // net_weight
        'PESO_BRUTO', // gross_weight
        'U_DIAMETRO', // diameter_id
        'U_ONZAS', // capacity_id
        'U_CAJA', // cartonsize
        'U_CANT_PAQ', // package_units
        'U_PAQ_CAJA', // package_box
        'U_UNID_CAJA', // box_units / laminate
        'U_CAJAS_CAMADA', // box_litter
        'U_CAMADA_PALETA', // platform_litter
        'U_CAJAS_PALETA', // dunnage_size
    ];

    protected $casts = [
        'ARTICULO'          => 'string',
        'DESCRIPCION'       => 'string',
        'ACTIVO'            => 'string',
        'CODIGO_BARRAS_VENT'=> 'string',
        'CLASIFICACION_1'   => 'string',
        'CLASIFICACION_2'   => 'string',
        'CLASIFICACION_3'   => 'string',
        'CLASIFICACION_4'   => 'string',
        'CLASIFICACION_5'   => 'string',
        'CLASIFICACION_6'   => 'string',
        'PESO_NETO'         => 'float',
        'PESO_BRUTO'        => 'float',
        'U_DIAMETRO'        => 'string',
        'U_ONZAS'           => 'string',
        'U_CAJA'            => 'string',
        'U_CANT_PAQ'        => 'float',
        'U_PAQ_CAJA'        => 'float',
        'U_UNID_CAJA'       => 'float',
        'U_CAJAS_CAMADA'    => 'float',
        'U_CAMADA_PALETA'   => 'float',
        'U_CAJAS_PALETA'    => 'float',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'ARTICULO';
    }

    // ===== ACCESSORS para compatibilidad con código existente =====
    public function getIdAttribute()
    {
        return $this->ARTICULO;
    }

    public function getCodeAttribute()
    {
        return $this->ARTICULO;
    }

    public function getNameAttribute()
    {
        return $this->DESCRIPCION;
    }

    public function getStatusAttribute()
    {
        return $this->ACTIVO === 'S';
    }

    public function getTypeAttribute()
    {
        return $this->TIPO;
    }

    public function getTypeNameAttribute()
    {
        $articleType = \App\Models\ArticleType::find($this->TIPO);
        return $articleType ? $articleType->name : $this->TIPO;
    }

    public function getBarcodeAttribute()
    {
        return $this->CODIGO_BARRAS_VENT;
    }

    public function getCategoryIdAttribute()
    {
        return $this->CLASIFICACION_1;
    }

    public function getProcesoIdAttribute()
    {
        return $this->CLASIFICACION_2;
    }

    public function getColorIdAttribute()
    {
        return $this->CLASIFICACION_6;
    }

    public function getMaterialIdAttribute()
    {
        return $this->CLASIFICACION_5;
    }

    public function getSubproductIdAttribute()
    {
        return $this->CLASIFICACION_4;
    }

    public function getFamilyproductIdAttribute()
    {
        return $this->CLASIFICACION_5;
    }

    public function getDiameterIdAttribute()
    {
        return $this->U_DIAMETRO;
    }

    public function getCapacityIdAttribute()
    {
        return $this->U_MEDIDA;
    }

    /**
     * Simula relación capacity con U_MEDIDA (esquema dinámico)
     */
    public function getCapacityAttribute()
    {
        if (!$this->U_MEDIDA) {
            return (object)['capacity' => 'N/A', 'name' => 'N/A'];
        }

        $schema = \App\Helpers\SchemaHelper::getSchema();
        $medida = \DB::connection('softland')
            ->table($schema . '.U_MEDIDA')
            ->where('U_CODIGO', $this->U_MEDIDA)
            ->first();

        return (object)[
            'capacity' => $medida ? $medida->U_DESCRIP : 'N/A',
            'name' => $medida ? $medida->U_DESCRIP : 'N/A'
        ];
    }

    public function getCartonsizeAttribute()
    {
        return $this->U_CAJA;
    }

    public function getPackageUnitsAttribute()
    {
        return $this->U_CANT_PAQ;
    }

    public function getPackageBoxAttribute()
    {
        return $this->U_PAQ_CAJA;
    }

    public function getBoxUnitsAttribute()
    {
        return $this->U_UNID_CAJA;
    }

    public function getLaminateAttribute()
    {
        return $this->U_UNID_CAJA;
    }

    public function getBoxLitterAttribute()
    {
        return $this->U_CAJAS_CAMADA;
    }

    public function getPlatformLitterAttribute()
    {
        return $this->U_CAMADA_PALETA;
    }

    public function getDunnageSizeAttribute()
    {
        return $this->U_CAJAS_PALETA;
    }

    public function getNetWeightAttribute()
    {
        return $this->PESO_NETO;
    }

    public function getGrossWeightAttribute()
    {
        return $this->PESO_BRUTO;
    }

    /**
     * Accessor para width_lamination (U_LAMINA en Softland)
     */
    public function getWidthLaminationAttribute()
    {
        return $this->U_LAMINA;
    }

    /**
     * Accessor para caliber_lamination (U_ESPESOR en Softland)
     */
    public function getCaliberLaminationAttribute()
    {
        return $this->U_ESPESOR;
    }

    /**
     * Accessor para location_id (campo fijo por defecto)
     */
    public function getLocationIdAttribute()
    {
        // Retornar la ubicación por defecto o desde algún campo de Softland
        // Por ahora retornamos 1 como default
        return $this->U_NAVE ?? 1;
    }

    // ===== RELACIONES SIMULADAS CON ACCESSORS =====

    /**
     * Simula relación category usando CLASIFICACION_4 desde Softland (esquema dinámico)
     */
    public function getCategoryAttribute()
    {
        if (!$this->CLASIFICACION_4) {
            return (object)['name' => 'N/A'];
        }

        $schema = \App\Helpers\SchemaHelper::getSchema();
        $categoria = \DB::connection('softland')
            ->table($schema . '.CLASIFICACION')
            ->where('CLASIFICACION', $this->CLASIFICACION_4)
            ->where('AGRUPACION', '4')
            ->first();

        return (object)[
            'name' => $categoria ? $categoria->DESCRIPCION : 'N/A'
        ];
    }

    /**
     * Simula relación material usando CLASIFICACION_5 desde Softland (esquema dinámico)
     */
    public function getMaterialAttribute()
    {
        if (!$this->CLASIFICACION_5) {
            return (object)['name' => 'N/A'];
        }

        $schema = \App\Helpers\SchemaHelper::getSchema();
        $material = \DB::connection('softland')
            ->table($schema . '.CLASIFICACION')
            ->where('CLASIFICACION', $this->CLASIFICACION_5)
            ->where('AGRUPACION', '5')
            ->first();

        return (object)[
            'name' => $material ? $material->DESCRIPCION : 'N/A'
        ];
    }

    /**
     * Simula relación color usando CLASIFICACION_6 desde Softland (esquema dinámico)
     */
    public function getColorAttribute()
    {
        if (!$this->CLASIFICACION_6) {
            return (object)['name' => 'N/A'];
        }

        $schema = \App\Helpers\SchemaHelper::getSchema();
        $color = \DB::connection('softland')
            ->table($schema . '.CLASIFICACION')
            ->where('CLASIFICACION', $this->CLASIFICACION_6)
            ->where('AGRUPACION', '6')
            ->first();

        return (object)[
            'name' => $color ? $color->DESCRIPCION : 'N/A'
        ];
    }

    /**
     * Simula relación aplication (no existe en Softland)
     */
    public function getAplicationAttribute()
    {
        return (object)['name' => 'N/A'];
    }

    /**
     * Simula relación diameter (no existe estructura en Softland)
     */
    public function getDiameterAttribute()
    {
        return (object)['diameter' => $this->U_DIAMETRO ?? 0];
    }

    /**
     * Simula relación inche (no existe en Softland)
     */
    public function getIncheAttribute()
    {
        return (object)['name' => 'N/A'];
    }

    /**
     * Accessor para picture1 (no existe en Softland)
     */
    public function getPicture1Attribute()
    {
        return null;
    }

    /**
     * Accessor para picture2 (no existe en Softland)
     */
    public function getPicture2Attribute()
    {
        return null;
    }

    /**
     * Accessors para campos que no existen en Softland
     */
    public function getDesignAttribute()
    {
        return 'N/A';
    }

    public function getHoleAttribute()
    {
        return 'N/A';
    }

    public function getDivisionAttribute()
    {
        return 'N/A';
    }

    public function getCompositionAttribute()
    {
        return 'N/A';
    }

    public function getRawMaterialAttribute()
    {
        return 'N/A';
    }

    public function getUsefulLifeAttribute()
    {
        return 'N/A';
    }

    // ===== RELACIONES ORIGINALES (DEPRECATED) =====
    public function categoryRelation()
    {
        return $this->hasOne(Category::class, 'id', 'CLASIFICACION_1');
    }

    public function familyproduct()
    {
        // CORREGIDO: familia está en CLASIFICACION_3
        return $this->hasOne(FamilyProduct::class, 'CLASIFICACION', 'CLASIFICACION_3');
    }

    public function subproduct()
    {
        return $this->hasOne(SubProduct::class, 'CLASIFICACION', 'CLASIFICACION_4');
    }


    public function material()
    {
        // MIGRATED: Material ahora es de Softland CLASIFICACION con AGRUPACION=5
        return $this->hasOne(Material::class, 'CLASIFICACION', 'CLASIFICACION_5');
    }


    public function color()
    {
        return $this->hasOne(Color::class, 'CLASIFICACION', 'CLASIFICACION_6');
    }

    public function aplication()
    {
        // Pendiente: no hay mapeo directo
        return null;
    }

    public function capacity()
    {
        return $this->hasOne(Onza::class, 'U_CODIGO', 'U_ONZAS');
    }

    public function diameter()
    {
        // Pendiente: U_DIAMETRO es varchar, necesita crear modelo Diameter en Softland
        return null;
    }

    public function inche()
    {
        // Pendiente: no hay mapeo directo
        return null;
    }

    public function machine()
    {
        return $this->hasOne(Machine::class, 'U_CODIGO', 'machine_id');
    }

    /**
     * MIGRATED: Relación many-to-many con Machines
     * Ahora usa tabla pivot en Softland (C01.U_MAQUINAS_ARTICULOS)
     */
    /**
     * Relación con fotos del artículo
     * NUEVA: Relación con C01.ARTICULO_FOTO
     */
    public function fotos()
    {
        return $this->hasMany(ArticuloFoto::class, 'ARTICULO', 'ARTICULO')
                    ->orderBy('PRIORIDAD', 'asc');
    }

    /**
     * Obtener la primera foto (mayor prioridad)
     */
    public function getFotoPrincipalAttribute()
    {
        return $this->fotos()->first();
    }

    /**
     * Obtener todas las fotos ordenadas por prioridad
     */
    public function getFotosOrdenadasAttribute()
    {
        return $this->fotos;
    }

    public function machines()
    {
        // DEPRECATED: Esta relación ya no se usa, usar getMachinesAttribute()
        return $this->belongsToMany(Machine::class,'machines_products', 'product_id', 'machine_id');
    }

    /**
     * Obtener máquinas asociadas desde tabla pivot de Softland (esquema dinámico)
     * MIGRATED: Ahora lee desde U_MAQUINAS_ARTICULOS
     */
    public function getMachinesAttribute()
    {
        // Paso 1: Obtener machine codes desde Softland
        $schema = \App\Helpers\SchemaHelper::getSchema();
        $machineCodes = \DB::connection('softland')
            ->table($schema . '.U_MAQUINAS_ARTICULOS')
            ->where('ARTICULO', $this->ARTICULO)
            ->pluck('U_CODIGO');

        if ($machineCodes->isEmpty()) {
            return collect([]);
        }

        // Paso 2: Cargar máquinas desde Softland
        return Machine::whereIn('U_CODIGO', $machineCodes)->get();
    }

    public function requisitions()
    {
        return $this->belongsToMany(Requisition::class,'products_requisitions')->withPivot('requisition_id')->withTimestamps();
    }

    public function supplies()
    {
        return $this->belongsToMany(Supplie::class,'products_supplies')->withPivot('supplie_id','quantity')->withTimestamps();
    }

    // Softland no usa timestamps
    // public function getCreatedAtAttribute($value){
    //     return Carbon::parse($value)->format('d-m-Y H:i:s');
    // }

    // public function getUpdatedAtAttribute($value){
    //     return Carbon::parse($value)->format('d-m-Y H:i:s');
    // }

}
