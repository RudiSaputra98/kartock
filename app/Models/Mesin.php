<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    protected $table = 'mesin';
    protected $fillable = [
        "name","note",
    ];

    public function product(){
        return $this->hasMany(Product::class);
    }

    public function trPakai(){
    return $this->hasMany(TrPakai::class);
    }

}
