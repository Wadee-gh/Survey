<noscript>If you see this you have probably JavaScript deactivated. QstConn does not work without Javascript being activated in the browser!</noscript>
<div class="container-fluid welcome">
    <div class="row text-center">

        <div class="panel panel-primary" >
            <!-- Header -->
            <div class="panel-body">
                <div class="row">
                    <img alt="logo" id="profile-img" class="profile-img-card center-block" src="<?php echo LOGO_URL; ?>" />
                </div>
            </div>
            <div class="row login-title login-content">
                <div class="col-lg-12">
                    <h3>Terms Of Service</h3>
                </div>
            </div>
            <div class="row login-content login-content-form">
                <div class="col-lg-12">
                    
                    <p>
                        <?= $tos->tos ?>
                    </p>
                </div>
            </div>
            <!-- Buttons -->
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'terms-of-service',
                'htmlOptions' => array(
                    'class' => 'form-container',
                ),
            ));
            ?>
            <div class="row login-submit login-content">
                <div class="col-lg-12">
                    <input type="hidden" name="action" value="agree" />
                    <input class="action btn btn-default" type="submit" value="<?php eT('I Read & Agree '); ?>" />
                    <br/><br/>
                </div>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
