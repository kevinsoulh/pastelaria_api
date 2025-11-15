<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description', 
        'price',
        'category',
        'photo',
        'is_available'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    /**
     * Attributes to append to the model's array form.
     */
    protected $appends = ['photo_url'];

    public function orders()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    /**
     * Get orders with customer data preloaded
     */
    public function ordersWithCustomers()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps()
                    ->with('customer');
    }

    /**
     * Scope for available products only
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Get the full URL for the product photo
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
