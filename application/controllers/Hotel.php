<?php
/**
 * Created by PhpStorm.
 * User: guoxiaosong
 * Date: 15/6/19
 * Time: 下午1:39
 */
class HotelController extends BaseController
{
    public function indexAction()
    {
        $models = HotelModel::paginate(20);

        $this->getLayout()->pushArrayVar('breadcrumb', 'hotels');
        $this->getView()->assign([
            'models' => $models
        ]);
    }
}