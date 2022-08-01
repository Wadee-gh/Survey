<div class='menubar surveymanagerbar'>
    <div class='row container-fluid'>
        <div class="col-xs-12 col-md-12">
            <h3 id="survey_title">
            <?php if (Permission::model()->hasGlobalPermission('superadmin')){?>
                <a href="<?php echo Yii::app()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid);?>"><?php echo $title_bar['title']; ?></a>
            <?php }else{ ?>
                <a href="<?php echo Yii::app()->createUrl('admin/participants/displayParticipants');?>">Home</a>
            <?php } ?>
            </h3>
        </div>
    </div>
</div>
