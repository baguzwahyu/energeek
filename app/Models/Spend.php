<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spend extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function details() {
		return $this->hasMany('App\Models\SpendDetail','spend_id');
	}
}
