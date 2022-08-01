<h3 class="pagetitle"><?php eT("Editing company");?></h3>

<div class="row" style="margin-bottom: 100px">
<div class="col-lg-12 content-right">
<?php echo CHtml::form(array("admin/companies/sa/moduser"), 'post', array('name'=>'moduserform', 'id'=>'moduserform','class'=>'form-horizontal')); ?>
<input type='hidden' name='uid' value="<?php echo $aUserData['uid'];?>" />
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Name");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('name',$aUserData['name'],array('class'=>"form-control"));?>
    </div>
</div>
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Branch");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('branch',$aUserData['branch'],array('class'=>"form-control"));?>
    </div>
</div>
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Address");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('address',$aUserData['address'],array('class'=>"form-control"));?>
    </div>
</div>
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Contact");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('contact',$aUserData['contact'],array('class'=>"form-control"));?>
    </div>
</div>
<!--<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Phone");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('phone',$aUserData['phone'],array('class'=>"form-control"));?>
    </div>
</div>
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Email");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('email',$aUserData['email'],array('class'=>"form-control"));?>
    </div>
</div>-->
<div class="form-group">
    <label for="user" class="col-sm-2 control-label"><?php eT("Website");?></label>
    <div class="col-sm-3">
        <?php echo CHtml::textField('website',$aUserData['website'],array('class'=>"form-control"));?>
    </div>
</div>
<p>
    <input type='submit' class="hidden" value='<?php eT("Save");?>' />
    <input type='hidden' name='action' value='moduser' />
</p>
</form>

