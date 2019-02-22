<?php
/**
 * Created by PhpStorm.
 * User: mamedov
 * Date: 11.02.2019
 * Time: 19:35
 */

namespace app\controllers;


use app\models\Film;
use app\models\Genres;
use app\models\User;
use app\models\Todo;
use core\base\Controller;
use core\base\TemplateView;
use core\base\View;
use core\db\DBQueryBuilder;

class Main extends Controller
{
    public function actionIndex(){
        $view = new TemplateView("main","templates/def");
        $view->films = Film::get();
        $view->hh="dfgdf";
        print_r(Film::where("name","Gone with the wind")->first()->genres()->get());
        $view->users_films = User::where("login","vasia")->first()->films()->get();
        //return $view;
    }
}