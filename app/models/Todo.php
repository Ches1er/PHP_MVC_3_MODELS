<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 19.02.2019
 * Time: 11:49
 */

namespace app\models;


class Todo extends \core\base\Model
{
    public $note_id;
    public $note_name;
    public $desc;
    public $user_id;

    protected static $table = "todo";

    public function user(){
        return $this->belongsTo(User::class,"user_id");
    }
}