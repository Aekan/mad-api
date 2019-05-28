<?php

namespace api\modules\v1\controllers;



use backend\controllers\Controller;
use backend\modules\Order\models\Order;
use backend\modules\Payment\controllers\PaymentController;
use backend\modules\Payment\models\Payment;
use backend\modules\Product\models\Product;
use backend\modules\ModulusCart\models\ModulusCart;
use backend\modules\Product\models\ProductSource;
use backend\modules\Reservations\models\Reservations;
use backend\modules\Reservations\models\ReservationsAdminSearchModel;
use Matrix\Exception;
use Yii;


/**
 * Class ArticleController
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ResterController extends Controller {
    public function actions(){
        return array(
            'index' => array(),
        );
    }

    public function beforeAction($action) {
        //if ("ipn" === Yii::$app->controller->action->id)
            $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionIndex($a, $b) {
        return $a + $b;
    }

    public function actionProduct($id = false) {
        $myProduct = $id ? Product::getProdById($id) : Product::getAllProducts();
        
        return $myProduct;
    }
    public function actionYell($selectedDate,$prodId){

        $currentSelftSp=new ReservationsAdminSearchModel();
        /**
        TODO: Handler if Product does not exitst
         *
         */
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
        $currentProduct=new Product();

        $currentProduct=Product::getProdById($prodId);
        $placesleft=$currentProduct->capacity-$counter;
        if($placesleft%2!=0){
            $placesleft-=1;
        }
        return $placesleft;


    }
    public function actionCcart($id) {
        $newCart= new ModulusCart();
        $values=['id'=>$id,'createDate'=>date('Y-m-d'),];


        return  ModulusCart::insertOne($newCart,$values);

    }
    public function actionGetcart($id) {
        $query = ModulusCart::aSelect(ModulusCart::class, '*', ModulusCart::tableName(), 'id=' . $id);

        try {
            $model = $query->one();
        } catch (Exception $e) {
        }

        return  $model->items;

    }
    public function actionCcadd($id,$items) {

        $query = ModulusCart::aSelect(ModulusCart::class, '*', ModulusCart::tableName(), 'id="' . $id.'"');

        try {
            $model = $query->one();
        } catch (Exception $e) {
        }

        if(json_decode($model->items)){
          $currentItems=json_decode($model->items);
          $currentItems->items[]=$items;
        }
        else{
         $currentItems=json_decode('{"items":[]}');
         $currentItems->items[]=$items;

        }


        $values=[
            'id'=>$id,
            'items'=>$items,
        ];


        return  ModulusCart::insertOne($model,$values);

    }

    public function actionAddReservation($data) {
        $response = json_decode($data);

        $reservationsData = json_decode($response->data);
        $ids = [];

        foreach ($reservationsData as $i => $reservationData) {
            $reservation = new Reservations();

            $values = [
                'bookingId' => null,
                'source' => $response->source,
                'data' => json_encode($reservationData),
                'productId' => $response->productId,
                'invoiceDate' => date('Y-m-d'   ),
                'bookingDate' => $response->bookingDate,
            ];

            $ids[] = Reservations::insertOneReturn($reservation, $values);
        }

        return PaymentController::actionPay($ids);
    }

    public function actionGetOrder($id) {
        return Order::getOrderById($id);
    }

    public function actionBackref() {
        return PaymentController::actionBackref();
    }

    public static function actionTimeout() {
        return PaymentController::actionTimeout();
    }

    public static function actionIrn() {
        return PaymentController::actionIrn();
    }

    public static function actionIdn() {
        return PaymentController::actionIdn();
    }

    public static function actionIos() {
        return PaymentController::actionIos();
    }

    public static function actionIpn() {
        return PaymentController::actionIpn();
    }
}
