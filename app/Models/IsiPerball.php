<?php

namespace App\Models;

use App\Models\TrMasuk;
use Illuminate\Database\Eloquent\Model;

class IsiPerball extends Model
{
    protected $table = 'isi_perball';
    protected $fillable = [
        "name","note",
    ];

    public function product(){
        return $this->hasMany(Product::class);
    }

    public function trPakai(){
    return $this->hasMany(TrPakai::class);
}

    public function trMasuk(){
        return $this->hasMany(TrMasuk::class);
        }

}
