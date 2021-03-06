<?php

    namespace api\modules\v1\controllers;




    use backend\controllers\Controller;
    use backend\modules\Autoimport\models\Autoimport;
    use backend\modules\content\controllers\PageController;
    use backend\modules\Modulusbuilder\controllers\ModulusbuilderController;
    use backend\modules\Order\models\Order;
    use backend\modules\Payment\controllers\PaymentController;
    use backend\modules\Payment\models\Payment;
    use backend\modules\Product\models\Product;
    use backend\modules\ModulusCart\models\ModulusCart;
    use backend\modules\Product\models\ProductSource;
    use backend\modules\Reservations\models\Reservations;
    use backend\modules\Reservations\models\ReservationsAdminSearchModel;
    use League\Uri\PublicSuffix\CurlHttpClient;
    use Matrix\Exception;
    use Yii;
    use yii\helpers\BaseHtml;


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
        public function getBookings($siteUrl,$apiUrl){
            $date=date('Y-m-d',strtotime('yesterday'));
            $curlUrl = $siteUrl . $apiUrl.$date;
            /*$curl=curl_init($curlUrl);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);*/
            $curl = new CurlHttpClient();
            $response = $curl->getContent($curlUrl);
            if ($response != 0) {
                $response = $curl->getContent($curlUrl);
            }

            return $response;


        }

        public function importBookings($response,$siteUrl){
            $newBookings=[];
            $json=json_decode($response);

            $newReservation=new Reservations();
            $newCounter=0;
            $oldCounter=0;
            foreach($json as $index=>$one){
                $newBookings[]=(array)$one;

            }


            foreach($newBookings as $booking){
                $newReservation=new Reservations();
                $foundReservation=Reservations::find()->andFilterWhere(['=','source',$siteUrl])->andFilterWhere(['=','bookingId',$booking['bookingId']])->one();
                if($foundReservation){
                    $oldCounter+=1;
                }else{

                    Reservations::insertOne($newReservation,$booking);
                    $newCounter+=1;

                }

            }
            $returned['new']=$newCounter;
            $returned['old']=$oldCounter;
            return $returned;



        }


        public function actionRefreshbook($site=null){

            $importsToDo=Autoimport::find()->andFilterWhere(['=','active',1])->andFilterWhere(['=','type','booking'])->all();
            if($site){
                $importsToDo=Autoimport::find()->andFilterWhere(['=','active',1])->andFilterWhere(['=','siteUrl',$site])
                    ->andFilterWhere(['=','type','booking'])->all();
            }
            $returned=new \stdClass();
            foreach ($importsToDo as $import){
                $response=$this->getBookings($import->siteUrl,$import->apiUrl);
                $returned->response[$import->siteUrl]=$this->importBookings($response,$import->siteUrl);

            }





            return $returned;


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

        public static function actionGetPageBySlug($slug) {
            return PageController::getContent($slug);
        }

        /**
         * @param $slug
         * @return mixed|string
         * This is for the pagebulder
         */
        public static function actionGetPageHtmlBySlug($slug) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'text/html');
            return (PageController::getContent($slug))->body;
        }




        public static function actionUpdatePageHtmlSlug(){
            $content=Yii::$app->request->post('content');
            $slug=Yii::$app->request->post('slug');
            return PageController::setContent($slug,$content);
        }

        /**
         * @param $id
         *
         * @return mixed|string
         * This is for the pagebulder
         */
        public static function actionGetEmailHtmlById($id) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'text/html');

            $body = (ModulusbuilderController::getContent($id))->body;

//        $stripfromStartArray=[
//            '<html>',
//            '<head>',
//            '<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet">',
//            '<script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>',
//            '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>',
//        ];
//
//        foreach ($stripfromStartArray as $s) {
//            $body = $s . $body;
//        }

            $body = `<html>
            <head>,
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet">
            <script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>` . $body;

            return $body;
        }
        public static function actionUpdateEmailHtmlId(){
            $content=Yii::$app->request->post('content');
            $id=Yii::$app->request->post('id');
            return ModulusbuilderController::setContent($id,$content);
        }

    }
