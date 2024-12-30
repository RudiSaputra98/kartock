<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\IsiPerball;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrMasuk extends Model
{
    use HasFactory;

    protected $table = 'tr_masuk';
    protected $guarded = [];
    protected $dates = ['tanggal'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isiPerball()
    {
        return $this->belongsTo(IsiPerball::class);
    }

    public function getMasukBallDisplayAttribute()
    {
        return $this->masuk_ball === 0 || $this->masuk_ball === null ? '-' : $this->masuk_ball;
    }

    public function getMasukPcsDisplayAttribute()
    {
        return $this->masuk_pcs === 0 || $this->masuk_pcs === null ? '-' : $this->masuk_pcs;
    }

    public function getIsiPerballNameDisplayAttribute()
    {
        return $this->isiPerball && $this->isiPerball->name == 0 ? '-' : $this->isiPerball->name;
    }


    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal->format('d/m/Y');  // Format sesuai kebutuhan Anda
    }
}
