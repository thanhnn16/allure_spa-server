<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'alt_text', 'type'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function treatmentCategories()
    {
        return $this->hasMany(TreatmentCategory::class);
    }
}