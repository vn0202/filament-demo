<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = "categories";
    protected  $fillable = [
        'title','slug','category_id','user_id'
    ];

    //Relationship
    public  function  category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
public function articles()
{
    return $this->hasMany(Article::class,'category_id');
}
}
