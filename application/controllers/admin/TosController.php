<?php

class TosController extends Survey_Common_Action {

    public function update($id) {
        $model = $this->loadModel($id);
        $model->scenario = "update";
        if (isset($_POST['Tos'])) {
            $model->attributes = $_POST['Tos'];
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Terms of service update Successfully!');
                $this->getController()->redirect(array("/admin/tos/sa/index"));
            }
        }

        $this->_renderWrappedTemplate('tos', array('layout', 'update'), compact('model'));
    }

    public function index() {
        $request = Yii::app()->request;
        $model = new Tos('search');
        $model->attributes = $request->getPost('LoginLogs');
        $this->_renderWrappedTemplate('tos', array('layout', 'index'), compact('model'));
    }

    public function loadModel($id) {
        $model = Tos::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

}
