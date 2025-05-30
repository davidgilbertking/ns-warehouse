<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['event_id', 'item_id', 'quantity'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
