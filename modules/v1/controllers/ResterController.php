<?php

namespace api\modules\v1\controllers;



use backend\controllers\Controller;
use backend\modules\Product\models\Product;

/**
 * Class ArticleController
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ResterController extends Controller {


    public function actions()
    {

        return array(
            'index' => array(

            ),
        );
    }

    public function actionIndex($a,$b) {

        return $a+$b;
    }
    public function actionProduct($id){
        $myProduct=new Product();
        $myProduct=$myProduct->getProdById($id);
        return $myProduct;
    }



    public function actionYell(){

        return 'yellelek echoval de nem variablel';
    }
}