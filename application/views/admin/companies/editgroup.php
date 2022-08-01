<div id="edit_company_group" style="width: 80%">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'edit_company_group_form',
        'enableAjaxValidation' => false,
        'htmlOptions' => array(
            'class' => 'form-container',
        ),
    ));
    ?>

    <div class="col-md-12">
        <input type="hidden" name="company_group">
        <?php
        foreach ($groups as $group) {
            $checked = "";
            if (in_array($group->gid, $selected_groups)) {
                $checked = "checked='checked'";
            }
            ?>
            <div class="col-sm-4">
                <label>
                    <input <?= $checked ?> type="checkbox" id="check_group_<?= $group['gid'] ?>" name="groups[]" value="<?= $group['gid'] ?>">
                    <?= $group['group_name'] ?>
                </label>
            </div>
        <?php } ?>
    </div>
    <div class="col-md-9 col-md-offset-3 text-right">
        <input type="button" onclick="saveCompanyGroup()"  class="btn btn-primary " value="Save">
    </div>
    <?php $this->endWidget(); ?>
    <div class="col-md-12" id="edit_company_group_result" style="display:none">
        <div class="alert alert-success"> Group has been successfully updated! </div>
    </div>


    <script>
        function saveCompanyGroup() {
            $.ajax({
                url: '<?= Yii::app()->getController()->createUrl('/admin/companies/sa/group', array("id" => $company->uid)) ?>',
                type: "GET",
                data: $("#edit_company_group_form").serialize(),
                async: true,
                dataType: 'json',
                success: function (msg) {
                    if (msg.status == 1) {
                        $("#edit_company_group_form").hide();
                        $("#edit_company_group_result").show();
                        
                        setTimeout(function(){ $.fancybox.close(); }, 3000);

                    } else {
                        alert(msg.message);
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
    </script>
</div>
