<?php

namespace api\modules\v1\controllers;


use \JsonRpc2\Controller;
use backend\modules\Product\models\Product;

/**
 * Class ArticleController
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class WorkerController extends Controller {


    public function actions()
    {

        return array(
            'index' => array(
                'class' => \nizsheanez\jsonRpc\Action::class,
            ),
        );
    }

    public function actionSum($a,$b) {

        return $a+$b;
    }
    public function actionProduct($id){
        $myProduct=new Product();
        $myProduct=$myProduct->getProdById($id);
        return $myProduct;




    }
    public function yell($message){

        return 'yellelek echoval de nem variablel';
    }
}
