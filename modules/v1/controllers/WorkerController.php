<?php

namespace api\modules\v1\controllers;


use \JsonRpc2\Controller;
use backend\modules\Product\models\Product;
use Yii;
use yii\web\Response;

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

    public function behaviors() {
        $behaviors = parent::behaviors();

        $auth = $behaviors['authenticator'];

        // remove authentication filter necessary because we need to
        // add CORS filter and it should be added after the CORS
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => '\yii\filters\Cors',
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;

        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        return $behaviors;
    }

    public function beforeAction($action){
        Yii::$app->response->format = Response::FORMAT_JSON;
        return true;
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
