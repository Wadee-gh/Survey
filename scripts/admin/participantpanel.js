//Namespacing all Methods of the participant panel in one JS-Prototype
LS = LS || {};

function groupSelectionActions(){
  // add ajax action to display question groups in survey.
  $('select[name=survey_id].trigger').change(function(){
    var val = $(this).val();
    //console.log('survey_id:'+val);
    updateQuestionGroups(val);
  });

  var hasDefault = false;
  var defaultId = 0;
  var options = $('select[name=survey_id].trigger').find('option');
  options.each(function(){
    var name = $(this).text();
    var val = $(this).val();
    //console.log(name+' '+val);
    if(name == 'Default' || name == 'default'){
      defaultId = val;
    }
  });
  //console.log(defaultId);
  //$('#survey_div').children().hide();
  $('#survey_div').find('label').remove();
  $('#survey_div').children().show();
  if(defaultId == 0){
    $('#survey_div #survey_text').html('No survey named \'Default\' found.');
    $('#survey_div #survey_text').show();
    $('.action_save_modal_customParticipant').hide();
  } else {
    $('select[name=survey_id].trigger').val(defaultId);
    $('select[name=survey_id].trigger').trigger('change');
  }
}

function updateQuestionGroups(surveyid){
  action = $('select[name=survey_id].trigger').attr('action');
  var postdata = {
      surveyid: surveyid,
      YII_CSRF_TOKEN : LS.data.csrfToken
  };
  $.ajax({
      url: action,
      data: postdata,
      method: 'POST',
      success: function(result) {
          //console.log(result);
          var r = jQuery.parseJSON(result);
          $('#required-groups-inner').html(r.html);
          $('#required-groups').show();
          if($('#email-div').length>0){
            $('#email-div').show();
          }
      },
      error : function() {
          console.log(arguments);
      }
  });
}

function repeat_last_groups(glist){
    $('#repeat_last_groups').hide();
    //alert(glist);
    var groups = glist.split(",");
    for (i = 0; i < groups.length; i++) {
        //alert(groups[i]);
        $('#check_group_'+groups[i]).prop('checked', true);
    }
}

LS.CPDB = (function() {
    var
    // Basic modal used by all submethods
    baseModal = '#participantPanel_edit_modal',

    /**
     * @param {string} url
     * @param {object} data
     * @param {string} actionButtonClass
     * @param {string} formId
     * @oaram {string} gridViewId
     * @return
     */
    runBaseModal = function(url, data, actionButtonClass, formId, gridViewId){

        var secondSuccess = function(result) {
            $(baseModal).modal('hide');
            //console.log('secondsuccess');
            $.fn.yiiGridView.update(gridViewId,{});
            runPostAjaxTasks(formId);
        };

        function runPostAjaxTasks(formId){
          if(formId == 'customParticipantActiveForm'){
            var form = $('#'+formId);
            var redirect = form.find('input[name=redirect]').val();
            if(redirect.length>0){
              window.location.href = redirect;
            }
          }
        }

        function postSecond(action,formData){
            LS.ajax({
                url: action,
                data: formData,
                method: 'POST',
                success: secondSuccess,
                error : function() {
                    console.log(arguments);
                }
            });
        }

        function switchToEditParticipant(obj){
            /*$('.action_participant_editModal').on('click', function(e){
                e.preventDefault();*/
                var data = {modalTarget: 'editparticipant', 'participant_id' : obj.participant_id};
                //url, data, idString, actionButtonClass, formId, gridViewId
                runBaseModal(
                    openModalParticipantPanel,
                    data,
                    'action_save_modal_editParticipant',
                    'editPartcipantActiveForm',
                    'list_central_participants'
                ).done(function() {
                    var val = $('#participantPanel_edit_modal .ls-bootstrap-switch').attr('checked');
                    $('.ls-bootstrap-switch').bootstrapSwitch('state', val == 'checked');
                });
            //});
        }

        function switchToSendEmailRequest(obj){
            //e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'sendEmailRequest',
                title2: 'Send Email Request',
                participant_id: obj.participant_id
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        }

        function checkParticipantExists(action,formData,process){
            $("#editPartcipantActiveForm .form-control").removeClass('error')
            $.ajax({
                url: 'sa/participantExists',
                data: formData,
                method: 'POST',
                success: function(result) {
                    //alert(result);
                    var obj = jQuery.parseJSON(result);
                    errorMessage = obj.errorMessage;
                    if(obj.hasError == 1){
                        $.each(errorMessage, function( index, value ) {
                            $("#editPartcipantActiveForm ."+index).addClass('error')
                        });
                        return;
                    }else{
                        if(obj.exists){
                          if(confirm("User already exists. Use existing profile?")){
                            if(process == 'addParticipant'){
                              // switch to Edit Participant instead of Add Participant.
                              switchToEditParticipant(obj.row);
                            } else
                            if(process == 'sendEmailRequest'){
                              switchToSendEmailRequest(obj.row);
                            }
                          }
                        } else {
                          // close the dialog and do nothing.
                          postSecond(action,formData);
                        }
                    }
                },
                error : function() {
                    console.log(arguments);
                }
            });
        }

        /**
         * @param {string} page - Modal HTML fetched with Ajax
         * @todo
         */
        var firstSuccess = function(json){
            $(baseModal).find('.modal-content').html(json.result);
            $(baseModal).modal('show');
            $(baseModal).find('.'+actionButtonClass).on('click', function(e) {
                e.preventDefault();
                var action = $(baseModal).find('#'+formId).attr('action');
                var formData = $(baseModal).find('#'+formId).serializeArray();
                //console.log('firstsuccess');
                //console.log(action);
                //alert(actionButtonClass);
                if(actionButtonClass == 'action_save_modal_editParticipant'){
                  var oper = 'edit';
                  if(data.participant_id == undefined){
                    oper = 'add';
                  }
                  //alert(formId + ' ' + data.participant_id + ' ' + oper);
                  if(oper == 'add'){
                    checkParticipantExists(action,formData,'addParticipant');
                  } else {
                    postSecond(action,formData);
                  }
                } else
                if(actionButtonClass == 'action_save_modal_customParticipant'){
                  var process = data.process;
                  postSecond(action,formData);
//                  if(process == 'sendEmailRequest'){
//                    checkParticipantExists(action,formData,process);
//                  } else {
//                    postSecond(action,formData);
//                  }
                } else {
                  postSecond(action,formData);
                }

            });
        };

        return LS.ajax({
            url: url,
            data: data,
            method: 'POST',
            success: firstSuccess,
            error: console.log
        });
    },

    /** added by mark mirasol 7/16/2018 **/

    /**
     * Run when user clicks 'Print'
     * Used for both all participants and checked participants
     * @param {boolean} all - If true, export all participants
     * @return
     */
    onClickExport2 = function(all) {
        var postdata = {
            selectedParticipant: [],
            YII_CSRF_TOKEN : LS.data.csrfToken
        };
        //alert('hello');
        if (!all) {
            $('.selector_participantCheckbox:checked').each(function(i,item){
                postdata.selectedParticipant.push($(item).val());
            });
        }

        function add_interval_actions(){
          $('#interval').change(function(){
            var val = $(this).val();
            //alert(val);
            if(val == 'date_range'){
              $('.date_range').show();
            } else {
              $('.date_range').hide();
            }
          });
        }

        function submit_print_form(){
            var form = $('#exportpdf_form');
            form.attr('action',exporttopdf);
            form.attr('method','POST');
            $('[name=YII_CSRF_TOKEN]').val(LS.data.csrfToken);
            form.submit();
        }

        $.ajax({
            url: exporttocsvcountall,
            data: postdata,
            method: 'POST',

            /**
             * @param {string} data
             * @return
             */
            success:  function(data) {
                count = data;
                if(count == 0)
                {
                    $('#exportcsvallnorow').modal('show');
                    $('#exportcsvallnorow').on('shown.bs.modal', function(e) {
                        $(this).find('.exportButton').remove();
                    });
                }
                else
                {
                    $('#exportpdf [name=participants]').val(postdata.selectedParticipant.toString());
                    $('#exportpdf').modal('show');
                    $('#exportpdf').on('shown.bs.modal', function(e) {
                        var self = this;
                        var title = count.replace(' to CSV','').replace('Export','Print result for');
                        add_interval_actions();
                        $(this).find('h4.modal-title').text(title);
                        $(this).find('.printButton').on('click', function() {
                            submit_print_form();
                            $(self).modal("hide");
                        });
                    });
                    /* $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));*/
                }
            }
        });
    },

    /** end of added by mark mirasol 7/16/2018 **/

    /**
     * Run when user clicks 'Export'
     * Used for both all participants and checked participants
     * @param {boolean} all - If true, export all participants
     * @return
     */
    onClickExport = function(all) {
        //alert('hello');
        var postdata = {
            selectedParticipant: [],
            YII_CSRF_TOKEN : LS.data.csrfToken
        };

        if (!all) {
            $('.selector_participantCheckbox:checked').each(function(i,item){
                postdata.selectedParticipant.push($(item).val());
            });
        }

        $.ajax({
            url: exporttocsvcountall,
            data: postdata,
            method: 'POST',

            /**
             * @param {string} data
             * @return
             */
            success:  function(data) {
                count = data;
                if(count == 0)
                {
                    $('#exportcsvallnorow').modal('show');
                    $('#exportcsvallnorow').on('shown.bs.modal', function(e) {
                        $(this).find('.exportButton').remove();
                    });
                }
                else
                {
                    $('#exportcsv').modal('show');
                    $('#exportcsv').on('shown.bs.modal', function(e) {
                        var self = this;
                        $(this).find('h4.modal-title').text(count);
                        $(this).find('.exportButton').on('click', function() {
                            var dldata = postdata;
                            var val = $('#attributes').val();
                            if (val) {
                                dldata.attributes = val.join('+');
                            }
                            else {
                                dldata.attributes = '';
                            }
                            var dlForm = $("<form></form>")
                                .attr('action', exportToCSVURL)
                                .attr('method', "POST");
                            $.each(dldata, function(key,value){
                                $('<input />')
                                    .attr('name', key)
                                    .attr('value', value)
                                    .appendTo(dlForm);
                            });
                            dlForm.submit();
                            $(self).modal("hide");
                        });
                        $('#attributes')
                            .multiselect({
                                includeSelectAllOption:true,
                                selectAllValue: '0',
                                selectAllText: sSelectAllText,
                                nonSelectedText: sNonSelectedText,
                                nSelectedText: sNSelectedText,
                                maxHeight: 140
                            });
                    });
                    /* $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));*/
                }
            }
        });
    },

    // Basic settings and bindings that should take place in all three views
    basics = function() {
        // Code for AJAX download
        jQuery.download = function(url, data, method){
            //url and data options required
            if( url && data ){
                //data can be string of parameters or array/object
                data = typeof data == 'string' ? data : jQuery.param(data);
                //split params into form inputs
                var inputs = '<input type="hidden" name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'">';
                jQuery.each(data.split('&'), function(){
                    var pair = this.split('=');
                    inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'">';
                });
                //send request
                jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
                    .appendTo('body').submit().remove();
            };
        };
    },

    // JS-bindings especially for the participantPanel
    participantPanel = function(){

        $('#removeAllFilters').on('click', function(e){
            e.preventDefault();
            $('#searchcondition').val('');
            $('#ParticipantFilters').remove();
            $.fn.yiiGridView.update('list_central_participants',{});
            return false;
        });

        $('.action_participant_editModal').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editParticipant',
                'editPartcipantActiveForm',
                'list_central_participants'
            ).done(function() {
                var val = $('#participantPanel_edit_modal .ls-bootstrap-switch').attr('checked');
                $('.ls-bootstrap-switch').bootstrapSwitch('state', val == 'checked');
            });
        });

        $('.action_participant_deleteModal').on('click', function(e) {
            e.preventDefault();
            var data = {modalTarget: 'showdeleteparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                    openModalParticipantPanel,
                    data,
                    'action_save_modal_deleteParticipant',
                    'deleteParticipantActiveForm',
                    'list_central_participants'
                    );
        });
        $('.action_participant_infoModal').on('click', function(e) {
            e.preventDefault();
            var data = {
                modalTarget: 'showparticipantsurveys',
                participant_id: $(this).closest('tr').data('participant_id')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                    openModalParticipantPanel,
                    data,
                    'action_save_modal_deleteParticipant',
                    'deleteParticipantActiveForm',
                    'list_central_participants'
                    );
        });
        $('.action_participant_shareParticipant').on('click', function(e) {
            e.preventDefault();
            var data = {modalTarget: 'shareparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_shareparticipant',
                'shareParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
        });

        /** added by mark mirasol 2/8/2017 **/

        $('#onSiteAnonymousRequest').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'onSiteAnonymousRequest',
                title2: 'On Site Anonymous Request',
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        });

        $('#sendEmailRequest').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'sendEmailRequest',
                title2: 'Send Email Request',
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        });

        $('#sendOnSiteRequest').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'sendOnSiteRequest',
                title2: 'Send On Site Request',
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        });

        /** end of added by mark mirasol **/

        /** added by mark mirasol 2/27/2017 **/

        $('.action_participant_sendEmailRequest').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'sendEmailRequest',
                title2: 'Send Email Request',
                participant_id: $(this).closest('tr').data('participant_id')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        });

        $('.action_participant_onSiteRequest').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'customParticipant',
                process: 'sendOnSiteRequest',
                title2: 'Send On Site Request',
                participant_id: $(this).closest('tr').data('participant_id')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_customParticipant',
                'customParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
                groupSelectionActions();
            });
        });

        /** end of added by mark mirasol **/

        /** added by mark mirasol 3/24/2017 **/

        $('.action_view_response').on('click', function(e){
            e.preventDefault();
            //console.log('view response clicked.');
            action = $('#showrespurl').val();
            var postdata = {
                YII_CSRF_TOKEN : LS.data.csrfToken,
                participant_id: $(this).closest('tr').data('participant_id')
            };
            $.ajax({
                url: action,
                data: postdata,
                method: 'POST',
                success: function(result) {
                    console.log(result);
                    var r = jQuery.parseJSON(result);
                    if(r.status == 'success'){
                      window.location = r.url;
                    } else {
                      alert(r.message);
                    }
                },
                error : function() {
                    console.log(arguments);
                }
            });
        });

        /** end of added by mark mirasol **/

        /** added by mark mirasol 2/11/2017 **/

        function groupSelectionActions(){
          // add ajax action to display question groups in survey.
          $('select[name=survey_id].trigger').change(function(){
            var val = $(this).val();
            //console.log('survey_id:'+val);
            updateQuestionGroups(val);
          });

          var hasDefault = false;
          var defaultId = 0;
          var options = $('select[name=survey_id].trigger').find('option');
          options.each(function(){
            var name = $(this).text();
            var val = $(this).val();
            //console.log(name+' '+val);
            if(name == 'Default' || name == 'default'){
              defaultId = val;
            }
          });
          //console.log(defaultId);
          //$('#survey_div').children().hide();
          $('#survey_div').find('label').remove();
          $('#survey_div').children().show();
          if(defaultId == 0){
            $('#survey_div #survey_text').html('No survey named \'Default\' found.');
            $('#survey_div #survey_text').show();
            $('.action_save_modal_customParticipant').hide();
          } else {
            $('select[name=survey_id].trigger').val(defaultId);
            $('select[name=survey_id].trigger').trigger('change');
          }
        }

        function updateQuestionGroups(surveyid){
          action = $('select[name=survey_id].trigger').attr('action');
          var postdata = {
              surveyid: surveyid,
              YII_CSRF_TOKEN : LS.data.csrfToken
          };
          $.ajax({
              url: action,
              data: postdata,
              method: 'POST',
              success: function(result) {
                  //console.log(result);
                  var r = jQuery.parseJSON(result);
                  $('#required-groups-inner').html(r.html);
                  $('#required-groups').show();
                  if($('#email-div').length>0){
                    $('#email-div').show();
                  }
              },
              error : function() {
                  console.log(arguments);
              }
          });
        }

        /** end of added by mark mirasol **/
        $("#addParticipantToCPP").unbind( "click" );
        $('#addParticipantToCPP').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'editparticipant'
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editParticipant',
                'editPartcipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
        });

        /**
         * Small icon, add participant to a survey
         */
        $('.action_participant_addToSurvey').on('click', function(e) {
            var data = {
                modalTarget: 'addToSurvey',
                participant_id: $(this).closest('tr').data('participant_id')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_addToSurvey',
                'addToSurveyActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
        });

        // Toggle all, participant list
        $('#action_toggleAllParticipant').on('click', function() {
            $('.selector_participantCheckbox').prop('checked',$('#action_toggleAllParticipant').prop('checked'));
        });

        // Toggle all, share panel
        $('#action_toggleAllParticipantShare').on('click', function() {
            $('.selector_participantShareCheckbox').prop('checked', $('#action_toggleAllParticipantShare').prop('checked'));
        });

        if(($('#pageSizeParticipantView').val() <= 100) || ($('#pageSizeAttributes').val() <= 100) || ($('#pageSizeShareParticipantView').val() <= 100) ){
            $('.action_changeBlacklistStatus').bootstrapSwitch();
        }

        $('.action_changeBlacklistStatus').on('switchChange.bootstrapSwitch', function(event,state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel,
                method: "POST",
                data: {actionTarget: 'changeBlacklistStatus', 'participant_id': $(self).closest('tr').data('participant_id'), 'blacklist': state},
                dataType: 'json',
                success: function(resolve){
                    $(self).prop("checked", (resolve.newValue == "Y"));
                }
            })
        });

        if(($('#pageSizeParticipantView').val() <= 100) || ($('#pageSizeAttributes').val() <= 100) || ($('#pageSizeShareParticipantView').val() <= 100) ){
            $('.action_changeActiveStatus').bootstrapSwitch();
        }

        $('.action_changeActiveStatus').on('switchChange.bootstrapSwitch', function(event,state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel,
                method: "POST",
                data: {actionTarget: 'changeActiveStatus', 'participant_id': $(self).closest('tr').data('participant_id'), 'active': state},
                dataType: 'json',
                success: function(resolve){
                    $(self).prop("checked", (resolve.newValue == "Y"));
                }
            })
        });


        $('#pageSizeParticipantView').on("change", function(){
            $.fn.yiiGridView.update('list_central_participants',{ data:{ pageSizeParticipantView: $(this).val() }});
        });

    },
    //JS-bindings especially for the attributePanel
    attributePanel = function(){
        $('#addParticipantAttributeName').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editattribute'};
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editAttributeName',
                'editAttributeNameActiveForm',
                'list_attributes'
            );
        });
        $('.action_attributeNames_editModal').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editattribute','attribute_id' : $(this).closest('tr').data('attribute_id')};
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editAttributeName',
                'editAttributeNameActiveForm',
                'list_attributes'
            );
        });

        $('#action_toggleAllAttributeNames').on('click', function(){
            $('.selector_attributeNamesCheckbox').prop('checked',$('#action_toggleAllAttributeNames').prop('checked'));
        });

        $('.action_changeAttributeVisibility').bootstrapSwitch();
        $('.action_changeAttributeVisibility').on('switchChange.bootstrapSwitch', function(event,state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel,
                method: "POST",
                data: { actionTarget: 'changeAttributeVisibility', 'attribute_id': $(self).closest('tr').data('attribute_id'), 'visible': state},
                dataType: 'json',
                success: function(resolve){
                    $(self).prop("checked", (resolve.newValue == "Y"));
                }
            })
        });
    },
    //JS-bindings especially for the sharePanel
    sharePanel = function() {
        $('#action_toggleAllParticipant').on('click', function(){
            $('.selector_participantCheckbox').prop('checked', $('#action_toggleAllParticipant').prop('checked'));
        });

        $('.action_changeEditableStatus').bootstrapSwitch();

        $('.action_changeEditableStatus').on('switchChange.bootstrapSwitch', function(event, state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel,
                method: "POST",
                data: {actionTarget: 'changeSharedEditableStatus', 'participant_id': $(self).closest('tr').data('participant_id'), 'can_edit': state},
                dataType: 'json',
                success: function(resolve){
                    $(self).prop("checked", resolve.newValue);
                }
            })
        });

        $('#pageSizeShareParticipantView').on("change", function(){
            $.fn.yiiGridView.update('share_central_participants',{ data:{ pageSizeShareParticipantView: $(this).val() }});
        });
    },

    /**
     * Modal for sharing checked items, massive action
     * @param {array} participantIds - Array of participant ids
     * @return
     */
    shareMassiveAction = function(participantIds) {
        var data = {
            modalTarget: 'shareparticipant',
            participantIds: participantIds
        };
        runBaseModal(
            openModalParticipantPanel,
            data,
            'action_save_modal_shareparticipant',
            'shareParticipantActiveForm',
            'list_central_participants'
        ).done(function() {
            $('.ls-bootstrap-switch').bootstrapSwitch();
        });
    },

    /**
     * Modal for adding participants to a survey.
     * Used by massive action.
     * @param {array} participantIds - Array of participant ids
     * @return
     */
    addParticipantToSurvey = function(participantIds) {
        var data = {
            modalTarget: 'addToSurvey',
            participant_id: participantIds.join(',')
        };
        runBaseModal(
            openModalParticipantPanel,
            data,
            'action_save_modal_addToSurvey',
            'addToSurveyActiveForm',
            'list_central_participants'
        ).done(function() {
            $('.ls-bootstrap-switch').bootstrapSwitch();
        });
    },

    /**
     * Call server to delete ONE single participant share
     * @param {string} participantId
     * @param {number} shareUid
     * @return
     */
    deleteSingleParticipantShare = function(url) {
        LS.ajax({
            url: url,
            method: "GET",
            dataType: 'json',
            success: function(result){
                $.fn.yiiGridView.update('share_central_participants',{});
            }
        });
    },

    /**
     * Bind all JS functions to button clicks
     * @return
     */
    bindButtons = function() {
        basics();
        switch($('#locator').data('location')){
            case 'participants' : participantPanel(); break;
            case 'attributes' :  attributePanel(); break;
            case 'sharepanel' :  sharePanel(); break;
        }

        /**
         * @TODO rewrite export
         */
        $('#export').click(function() { onClickExport(true); });

        doToolTip();
    };

    return {
        basics: basics,
        runBaseModal: runBaseModal,
        participantPanel: participantPanel,
        attributePanel: attributePanel,
        sharePanel: sharePanel,
        onClickExport: onClickExport,
        onClickExport2: onClickExport2,
        bindButtons: bindButtons,
        shareMassiveAction: shareMassiveAction,
        addParticipantToSurvey: addParticipantToSurvey,
        deleteSingleParticipantShare: deleteSingleParticipantShare
    };

})();

/**
 * ?
 */
function rejectParticipantShareAjax(participant_id){
    var runRejectParticipantShareAjax = function(){
        $.ajax({
            url: editValueParticipantPanel,
            data: {participant_id : participant_id, actionTarget: 'rejectShareParticipant'},
            method: "POST",
            dataType: 'json',
            success: function(result){
                notifyFader(result.successMessage, 'well-lg bg-primary text-center');
                $.fn.yiiGridView.update('share_central_participants',{});
            }
        })
    }
    return runRejectParticipantShareAjax;
}

/**
 * ?
 */
function deleteAttributeAjax(attribute_id){
    var runDeleteAttributeAjax = function(){
        $.ajax({
            url: editValueParticipantPanel,
            data: {attribute_id : attribute_id, actionTarget: 'deleteAttribute'},
            method: "POST",
            dataType: 'json',
            success: function(result){
                notifyFader(result.successMessage, 'well-lg bg-primary text-center');
                $.fn.yiiGridView.update('list_attributes',{});
            }
        })
    }
    return runDeleteAttributeAjax;
}

/**
 * ?
 */
function insertSearchCondition(id, options){
    options.data.searchcondition=$('#searchcondition').val();
    return options;
}

$(document).ready(LS.CPDB.bindButtons);
