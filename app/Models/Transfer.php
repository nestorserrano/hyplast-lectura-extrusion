<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transfer extends Model
{
    use HasFactory;
    protected $table = 'transfers';
    public $timestamps = true;
    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'date_storage','user_storage','status','pallets','created_at',
    ];

    protected $casts = [
        'id'                            => 'integer',
        'date_storage'                  => 'datetime',
        'user_storage'                  => 'integer',
        'status'                        => 'boolean',
        'pallets'                       => 'integer',
        'created_at'                    => 'datetime',
    ];

    public function userstorage()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function transferdetails(){
        return $this->hasMany(TransferDetails::class,'id', 'transfer_id');
    }

    public function user_storage()
    {
        return $this->hasOne(User::class, 'id', 'user_storage');
    }


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }


}
