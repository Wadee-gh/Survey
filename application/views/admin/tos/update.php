
<script src="//cdn.ckeditor.com/4.10.1/standard/ckeditor.js"></script>
<div class="col-xs-12">
    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
            <div class="row">
                <noscript>If you see this you have probably JavaScript deactivated. QstConn does not work without Javascript being activated in the browser!</noscript>
                <div class="container-fluid welcome">
                    <div class="row text-center">
                       <!-- Action Name -->
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="pull-left"><?php eT('Terms of service'); ?></h4>
                            </div>
                        </div>
                        <!-- Form -->                         
                        <?php
                        $form = $this->beginWidget('CActiveForm', array(
                            'id' => 'tos-form',
                            // Please note: When you enable ajax validation, make sure the corresponding
                            // controller action is handling ajax validation correctly.
                            // There is a call to performAjaxValidation() commented in generated controller code.
                            // See class documentation of CActiveForm for details on this.
                            'enableAjaxValidation' => false,
                            'htmlOptions' => array(
                                'class' => 'form-container',
                            ),
                        ));
                        ?>
                        <div class="row login-content login-content-form">
                            <div class="col-lg-12">
                                <span>
                                    <?php echo $form->textArea($model, 'tos',  array('rows' => 20, 'cols' => 50, 'class' => 'form-control spinner')); ?>
                                    <?php echo $form->error($model, 'tos'); ?>
                                </span>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="row login-submit login-content">
                            <div class="col-lg-12">
                                <input type="hidden" name="action" value="signup" />
                                <input class="action btn btn-default" type="submit" value="<?php eT('Update'); ?>" />
                                <br/><br/>

                            </div>

                        </div>
                        <?php $this->endWidget(); ?>

                    </div>
                </div>
                <style>
                    .errorMessage{
                        color : #FF0000
                    }
                </style>
            </div>
        </div>
    </div>
    <span id="locator" data-location="participants">&nbsp;</span>
</div>
<script>
    CKEDITOR.replace('Tos_tos', {
  height: 500
});
</script>