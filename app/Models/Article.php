<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
   protected $fillable = [
       'title','description','category_id','author','approvor','active','thumb','content'
   ];

    //Relationship

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }


    public function tags()
    {
        return $this->belongsToMany(Tag::class,'article_tag','article_id','tag_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class,'author');
    }


    public function change(Article $record)
    {
        $record->active = '1';
        $record->save();


    }

}
