<noscript>If you see this you have probably JavaScript deactivated. QstConn does not work without Javascript being activated in the browser!</noscript>
<div class="container-fluid welcome">
    <div class="row text-center">
        <div id="login-panel">
            <div class="panel panel-primary login-panel" id="panel-1">

                <!-- Header -->
                <div class="panel-body">
                    <div class="row">
                        <img alt="logo" id="profile-img" class="profile-img-card center-block" src="<?php echo LOGO_URL; ?>" />
                    </div>
                </div>

                <!-- Action Name -->
                <div class="row login-title login-content">
                    <div class="col-lg-12">
                        <h3><?php eT('Sign Up'); ?></h3>
                    </div>
                </div>

                <!-- Form -->
                <?php //echo CHtml::form(array("admin/authentication/sa/register"), 'post', array(s)); ?>
                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'booking-form',
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
                            <label for="email"><?php eT('Email'); ?></label>
                            <?php echo $form->textField($model, 'email', ['class' => 'form-control']); ?>
                            <?php echo $form->error($model, 'email'); ?>
                        </span>
                        <span>
                            <label for="password"><?php eT('Password'); ?></label>
                            <?php echo $form->passwordField($model, 'password', ['class' => 'form-control']); ?>
                            <?php echo $form->error($model, 'password'); ?>
                        </span>
                    </div>
                </div>
                <!-- Buttons -->
                <div class="row login-submit login-content">
                    <div class="col-lg-12">
                        <input type="hidden" name="action" value="signup" />
                        <input class="action btn btn-default" type="submit" value="<?php eT('Sign Up'); ?>" />
                        <br/><br/>

                    </div>

                </div>
                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>
</div>
<style>
    .errorMessage{
        color : #FF0000
    }
    </style>