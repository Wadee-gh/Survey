<?php
/**
 * Login Form
 */
?>
<noscript>If you see this you have probably JavaScript deactivated. QstConn does not work without Javascript being activated in the browser!</noscript>
<div class="container-fluid welcome">
    <div class="row text-center">
        <div id="login-panel">
            <div class="panel panel-primary login-panel" id="panel-1">

                <!-- Header -->
                <div class="panel-body">
                    <div class="row">
                          <img alt="logo" id="profile-img" class="profile-img-card center-block" src="<?php echo LOGO_URL;?>" />
                             <!--<p><?php eT("Administration");?></p>-->
                    </div>
                </div>

                <!-- Action Name -->
                <div class="row login-title login-content">
                      <div class="col-lg-12">
                       <h3><?php eT("Test Survey");?></h3>
                    </div>
                </div>

                <!-- Form -->
                <?php echo CHtml::form(array('admin/authentication/sa/login'), 'post', array('id'=>'loginform', 'name'=>'loginform'));?>
                    <div class="row login-content login-content-form">
                        <div class="col-lg-12">
                        <?php
                            //echo "<pre>".print_r($cparticipation,true)."</pre>"; //die();
                        ?>
                          <div class="form-group">
                            <label>What is your name?</label>
                            <input type="text" class="form-control" name="name" placeholder="John">
                          </div>
                          <div class="form-group">
                            <label>What is your age?</label>
                            <input type="text" class="form-control" name="age" placeholder="Doe">
                          </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="row login-submit login-content">
                        <div class="col-lg-12">
                                <p><input type='hidden' name='action' value='login' />
                                   <input type='hidden' id='width' name='width' value='' />
                                    <button type="submit" class="btn btn-default" name='login_submit' value='login'><?php eT('Submit');?></button><br />
                                    <br/>
                                </p>
                        </div>

                    </div>
                <?php echo CHtml::endForm(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Set focus on user input -->
<script type='text/javascript'>
$( document ).ready(function() {
    $('#user').focus();
    $("#width").val($(window).width());
});
</script>
