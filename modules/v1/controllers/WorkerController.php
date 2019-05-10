<?php

namespace api\modules\v1\controllers;


use backend\modules\Product\models\ProductSource;
use backend\modules\Reservations\models\ReservationsAdminSearchModel;
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
    public function actionYell($selectedDate,$prodId){
        $currentSelftSp=new ReservationsAdminSearchModel();
        $sources=ProductSource::getProductSourceIds($prodId);

        $what = ['*'];
        $from = $currentSelftSp::tableName();

        $wheres=[];
        $wheres[]=['bookingDate', '=', $selectedDate];
        $wheres[]=['productId', 'IN', $sources];
        $where = $currentSelftSp::andWhereFilter($wheres);

        $rows = $currentSelftSp::aSelect(ReservationsAdminSearchModel::class, $what, $from, $where,$sources,$prodId);

        $bookigsFromThatDay=$rows->all();
        $counter=0;
        foreach ($bookigsFromThatDay as $reservation){
            if(isset($reservation->bookedChairsCount)){
                $counter=$counter+$reservation->bookedChairsCount;

            }
        }
        $currentProduct=Product::getProdById($prodId);
        $placesleft=$currentProduct->capacity-$counter;
        if($placesleft%3!=0){
            $placesleft-=1;
        }
        return $placesleft;


    }
}
