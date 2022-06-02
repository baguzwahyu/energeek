<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function spend()
    {
        return $this->belongsTo('App\Models\Spend');
    }
}
