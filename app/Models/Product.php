<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_name',
        'price',
        'stock',
        'comment'
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getList()
    {
        $products = DB::table('products')->get();

        return $products;
    }

    public function registProduct($data)

    {
        DB::table('products')->insert([
            'product_name' => $data['product_name'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'comment' => $data['comment'],
        ]);
    }
    public function scopeCompanyId(Builder $query, $company_id): Builder
    {
        return $query->where('company_id', $company_id);
    }
    public function scopeProductName(Builder $query, $product_name): Builder
    {
        return $query->where('product_name', 'like', '%' . $product_name . '%');
    }
}
