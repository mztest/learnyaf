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

    public function viewAction()
    {
        $id = $this->getRequest()->get('id');
        $model = HotelModel::find($id);

        $this->getLayout()->pushArrayVar('breadcrumb', ['label'=>'hotels', 'url'=> '/hotel/index']);
        $this->getLayout()->pushArrayVar('breadcrumb', $model->name);
        $this->getView()->assign([
            'model' => $model
        ]);
    }
}