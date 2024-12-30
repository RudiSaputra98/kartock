<?php

namespace App\Models;

use App\Models\Mesin;
use App\Models\Category;
use App\Models\IsiPerball;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrPakai extends Model
{
    use HasFactory;
    protected $table = 'tr_pakai'; 
    protected $guarded = [];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function isiPerball(){
        return $this->belongsTo(IsiPerball::class);
    }

    public function mesin(){
        return $this->belongsTo(Mesin::class);
    }

    public function getPakaiBallDisplayAttribute()
    {
        return $this->pakai_ball === 0 || $this->pakai_ball === null ? '-' : $this->pakai_ball;
    }

    public function getPakaiPcsDisplayAttribute()
    {
        return $this->pakai_pcs === 0 || $this->pakai_pcs === null ? '-' : $this->pakai_pcs;
    }

    public function getIsiPerballNameDisplayAttribute()
    {
        return $this->isiPerball && $this->isiPerball->name == 0 ? '-' : $this->isiPerball->name;
    }

    public function getRejectDisplayAttribute()
    {
        return $this->reject === 0 || $this->reject === null ? '-' : $this->reject;
    }
}
