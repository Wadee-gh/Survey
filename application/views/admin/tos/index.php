<div class="col-xs-12">
    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
            <div class="row">
                <?php
              $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'list_central_participants',
                'emptyText'=>gT('No participants found.'),
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $model->columns,
                'rowHtmlOptionsExpression' => '["data-participant_id" => $data->id]',
                'filter'=>$model,
                'htmlOptions' => array('class'=> 'table-responsive'),
                'itemsCssClass' => 'table table-responsive table-striped',
                'afterAjaxUpdate' => 'LS.CPDB.bindButtons',
                'ajaxType' => 'POST',
                'beforeAjaxUpdate' => 'insertSearchCondition',
                //'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
//                'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
//                    CHtml::dropDownList(
//                        'pageSizeParticipantView',
//                        Yii::app()->user->getState('pageSizeParticipantView', Yii::app()->params['defaultPageSize']),
//                        Yii::app()->params['pageSizeOptionsTokens'],
//                        array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
//                    )
//                ),
            ));
                ?>
            </div>
        </div>
    </div>
    <span id="locator" data-location="participants">&nbsp;</span>
</div>