<?php
/**
 * Created by PhpStorm.
 * User: mamedov
 * Date: 18.02.2019
 * Time: 18:48
 */
namespace app\models;

class Film extends \core\base\Model
{
    public $id;
    public $name;
    public $year;
    public $user_id;

    protected static $table = "films";

    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
    }
    public function genres(){
        return $this->hasManyBelong(Genres::class,"films_genres",
            "id","genre_id","id","film_id");
    }
}