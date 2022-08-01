<?php

/**
 * This is the model class for table "{{tos}}".
 *
 * The followings are the available columns in table '{{tos}}':
 * @property integer $id
 * @property integer $company_id
 * @property string $tos
 * @property string $created_at
 * @property string $updated_at
 */
class Tos extends LSActiveRecord {

    public $uid = 1;

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{tos}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('company_id, tos', 'required','on'=>'update'),
            array('company_id', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, company_id, tos, created_at, updated_at', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'company' => array(self::BELONGS_TO, 'Company', 'company_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'company_id' => 'Company',
            'tos' => 'Tos',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search() {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;
        $criteria->with = 'company';
        $criteria->compare('id', $this->id);
        $criteria->compare('company_id', $this->company_id);
        $criteria->compare('tos', $this->tos, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('updated_at', $this->updated_at, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => array(
                'defaultOrder' => 't.id DESC',
            ),
        ));
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function getButtons() {
        $editUrl = Yii::app()->getController()->createUrl('/admin/tos/sa/update', array("id" => $this->id));
        $editUser = "<a title='" . gT("Edit this user") . "' href='" . $editUrl . "'
                class='btn btn-default btn-xs action_usercontrol_button'>
                    <span class='fa fa-pencil text-success'></span>
                </a>";
        return "<div>" . $editUser . "</div>";
    }

    public function getColumns() {
        $cols = array(
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                "filter" => false
            ),
            array("name" => 'id'),
            array(
                "name" => 'company_id',
                "value" => '$data->company->name'
            ),
            array('name' => 'created_at'),
            array('name' => 'updated_at')
        );
        return $cols;
    }

    public function beforeSave() {
        if ($this->isNewRecord) {
            $this->created_at = new CDbExpression('NOW()');
        }
        $this->updated_at = new CDbExpression('NOW()');
        return parent::beforeSave();
    }

}
