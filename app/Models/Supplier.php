<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'nama_pbf',
        'no_izin_pbf',
        'alamat',
        'kontak',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
