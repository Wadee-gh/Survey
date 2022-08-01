<?php

class Termsofservice extends Survey_Common_Action {

    public function index() {

        $user = User::model()->findByPk(Yii::app()->user->id);
        if ($user) {
            $tos = Tos::model()->findByPk($user->company_uid);
            if (Yii::app()->request->getPost('action')) {
                $user->accepted_tos = 1;
                $user->save();
                Yii::app()->session['accepted_tos'] = 1;
                $this->getController()->redirect(array("/admin/participants/displayParticipants"));
            }
            $this->_renderWrappedTemplate('tos', array('terms-of-service'), compact('tos'));
        }
    }

}
