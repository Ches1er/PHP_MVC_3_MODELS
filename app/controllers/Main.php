<?php
/**
 * Created by PhpStorm.
 * User: mamedov
 * Date: 11.02.2019
 * Time: 19:35
 */

namespace app\controllers;


use app\models\Film;
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
        /*INNER JOIN `films` on `users`.user_id = `films`.user_id
            INNER JOIN films_genres f_g on films.id = f_g.film_id
                INNER JOIN genres on f_g.genre_id = genres.id;*/
        $view->users_films = User::join("inner","films",["user_id","user_id"],
            [["films_genres","film_id","films","id"],
                ["genres","id","films_genres","genre_id"]])->all();
        $view->hh="dfgdf";
        return $view;
    }
}