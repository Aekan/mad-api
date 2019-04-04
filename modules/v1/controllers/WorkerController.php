<?php

namespace api\modules\v1\controllers;

use yii\rest\Controller;


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

    public function sum($a, $b) {
        return $a + $b;
    }
}
