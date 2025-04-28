<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_product')->withPivot('quantity');
    }
}
