<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'product_img',
        'product_price',
        'quantity',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
