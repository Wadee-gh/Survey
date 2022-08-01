<?php

class LoginLogsController extends Survey_Common_Action {

    public function Index() {
        $request = Yii::app()->request;
        $model = new LoginLogs('search');
        $model->attributes = $request->getPost('LoginLogs');
        Yii::app()->clientScript->registerPackage('bootstrap-switch');

        // Set page size
        if ($request->getPost('pageSizeParticipantView')) {
            Yii::app()->user->setState('pageSizeParticipantView', $request->getPost('pageSizeParticipantView'));
        }

        // Loads the participant panel view and display participant view
        $this->_renderWrappedTemplate('login_log', array('layout', 'data'), compact('model'));
    }

}
