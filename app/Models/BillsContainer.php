<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SoftlandModel;
use Carbon\Carbon;

class BillsContainer extends SoftlandModel
{
    use HasFactory;
    protected $table = 'FACTURA';
    protected $guarded = [
        'FACTURA',
    ];
    protected $fillable = [
        'tipo_documento', 'factura', 'fecha_despacho','fecha_recibido','pedido','fecha','fecha_entrega', 'anulada', 'embarcar_a', 'direccion_factura', 'observaciones', 'rubro1', 'rubro2', 'rubro3', 'rubro4', 'rubro5', 'cliente', 'pais', 'nombre_cliente',
    ];

    protected $casts = [

        'tipo_documento'        => 'string',
        'factura'               => 'integer',
        'fecha_despacho'        => 'datetime',
        'fecha_recibido'        => 'datetime',
        'pedido','fecha'        => 'datetime',
        'fecha_entrega'         => 'datetime',
        'anulada'               => 'boolean',
        'embarcar_a'            => 'string',
        'direccion_factura'     => 'string',
        'observaciones'         => 'string',
        'rubro1'                => 'string',
        'rubro2'                => 'string',
        'rubro3'                => 'string',
        'rubro4'                => 'string',
        'rubro5'                => 'string',
        'cliente'               => 'integer',
        'pais'                  => 'string',
        'nombre_cliente'        => 'string',
    ];

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'DOCUMENTO', 'FACTURA');
    }

}
