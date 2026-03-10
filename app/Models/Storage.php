<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Storage extends Model
{
    use HasFactory;
    protected $table = 'storages';
    public $timestamps = true;
    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'date_production','date_storage','codebar','box_litter','platform_litter','quantity','unit_weight','net_weight','gross_weight','total_weight','requisition_id','machine_id','product_id','user_production','user_storage', 'batch','transfer',
    ];

    protected $casts = [
        'id'                            => 'integer',
        'date_production'               => 'datetime:d-m-Y h:m:s A',
        'date_storage'                  => 'datetime',
        'codebar'                       => 'string',
        'box_litter'                    => 'integer',
        'platform_litter'               => 'integer',
        'quantity'                      => 'integer',
        'unit_weight'                   => 'decimal',
        'net_weight'                    => 'decimal',
        'gross_weight'                  => 'decimal',
        'total_weight'                  => 'decimal',
        'requisition_id'                => 'integer',
        'product_id'                    => 'string', // MIGRATED: ahora apunta a products.code
        'machine_id'                    => 'integer',
        'user_production'               => 'integer',
        'user_storage'                  => 'integer',
        'transfer'                      => 'boolean',
        'batch'                         => 'string',

    ];

    public function product()
    {
        // MIGRATED: ahora busca por ARTICULO (PK en Softland)
        return $this->hasOne(Product::class, 'ARTICULO', 'product_id');
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'id', 'requisition_id');
    }

    public function userproduction()
    {
        return $this->hasOne(User::class, 'id', 'user_production');
    }

    public function userstorage()
    {
        return $this->hasOne(User::class, 'id', 'user_storage');
    }

    public function machine()
    {
        return $this->hasOne(Machine::class, 'U_CODIGO', 'machine_id');
    }


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }


    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
