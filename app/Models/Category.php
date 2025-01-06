<?php

namespace App\Models;

use App\Models\TrMasuk;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        "name",
        "slug",
        "note",
    ];

    public function trPakai()
    {
        return $this->hasMany(TrPakai::class);
    }

    public function trMasuk()
    {
        return $this->hasMany(TrMasuk::class);
    }
}
