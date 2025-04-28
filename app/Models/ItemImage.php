<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    protected $fillable = [
        'item_id',
        'path',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
