<?php

class LoginLogs extends CActiveRecord {
    
    public $users_name;
    public function tableName() {
        return '{{login_logs}}';
    }

    public function rules() {
        return array(
            array('user_id, ip, login_time', 'required'),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('ip', 'length', 'max' => 255),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, user_id, ip, login_time, users_name', 'safe', 'on' => 'search'),
        );
    }

    public function relations() {
        return array(
            'user' => array(self::HAS_ONE, 'User', array('uid' => 'user_id')),
        );
    }

    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'ip' => 'Ip',
            'login_time' => 'Login Time',
        );
    }

    public function search() {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;
        $criteria->with = "user";
        $criteria->compare('id', $this->id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('ip', $this->ip, true);
        $criteria->compare('login_time', $this->login_time, true);
        $pageSize = Yii::app()->user->getState('pageSizeParticipantView', Yii::app()->params['defaultPageSize']);
        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => array(
                'defaultOrder' => 't.id DESC',
            ),
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    public function loginSuccess() {
        $model = new LoginLogs();
        $model->ip = Yii::app()->request->getUserHostAddress();
        $model->login_time = date("Y-m-d H:i:s");
        $model->user_id = Yii::app()->user->id;
        $model->save();
    }

    public function getColumns() {
        return array(
            array(
                "name" => 'login_time'
            ),
            array(
                "name" => 'ip',
                'header' => "User IP"
            ),
            array(
                'name' => 'user_id',
                'header'=>'Full Name',
                'value' => '$data->user->full_name',
                "filter" => LoginLogs::model()->allUsers()
            ),
            array(
                'name' => 'users_name',
                'value' => '$data->user->users_name',
                'filter'=>false
                //"filter" => LoginLogs::model()->allUsers()
            ),
            array(
                'header' => 'Company',
                'value' => '$data->user->companyName',
            )
        );
    }
    function allUsers() {
        return CHtml::listData(User::model()->findAll(), 'uid', 'full_name');
    }

}
