<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'birth_date',
        'address',
        'complement',
        'neighborhood',
        'zip_code',
    ];

    protected $casts = [
        'birth_date' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get orders with products preloaded
     */
    public function ordersWithProducts()
    {
        return $this->hasMany(Order::class)->with('products');
    }

    /**
     * Get customer with all related data
     */
    public function scopeWithCompleteRelations($query)
    {
        return $query->with(['orders.products']);
    }
}
