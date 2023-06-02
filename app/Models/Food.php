<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Food extends Model
{
    //softdelete berfungsi untuk penghapusan sementara, data masih tersedia pada tabel tetapi data tidak benar2 terhapus hanya diberikan tanda pada database.
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','description','ingredients','price','rate','types',
        'picturePath'
    ];

    public function getCreatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['picturePath'] = $this->picturePath;
        return $toArray;
    }

    public function getPicturePathAttribute()
    {
        return url ('') . Storage::url($this->attributes['picturePath']);
    }
}
