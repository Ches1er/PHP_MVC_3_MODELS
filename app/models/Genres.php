<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 20.02.2019
 * Time: 15:14
 */

namespace app\models;


use core\base\Model;

class Genres extends Model
{
    public $id;
    public $genre;

    protected static $table = "genres";
}