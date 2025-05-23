<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'size',
        'material',
        'supplier',
        'storage_location',
        'mechanics',
        'scalability',
        'client_price',
        'branding_options',
        'adaptation_options',
        'op_price',
        'construction_description',
        'contractor',
        'production_cost',
        'change_history',
        'consumables',
        'implementation_comments',
        'mounting',
        'storage_features',
        'design_links',
        'event_history',
        'storage_place',
        'op_media',
        'real_media',
        'event_media',
    ];

    protected $casts = [
        'op_media' => 'array',
        'real_media' => 'array',
        'event_media' => 'array',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    public function availableQuantity($startDate = null, $endDate = null)
    {
        $reserved = $this->reservations()
                         ->whereHas('event', function ($query) use ($startDate, $endDate) {
                             if ($startDate) {
                                 $query->where('end_date', '>=', $startDate);
                             }
                             if ($endDate) {
                                 $query->where('start_date', '<=', $endDate);
                             }
                         })
                         ->sum('quantity');

        return max(0, $this->quantity - $reserved);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'item_product')->withPivot('quantity');
    }
}
