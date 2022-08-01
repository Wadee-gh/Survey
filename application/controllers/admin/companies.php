<?php
use ls\pluginmanager\AuthPluginBase;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
* QstConn
* Copyright (C) 2013 The QstConn Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* QstConn is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* User Controller
*
* This controller performs user actions
*
* @package        QstConn
* @subpackage    Backend
*/
class Companies extends Survey_Common_Action
{
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('database');
    }

    /**
    * Get Post- or Paramvalue depending on where to get it
    * @param string $param
    * @return string
    */
    private function _getPostOrParam($param){
        $value = Yii::app()->request->getPost($param);
        if(!$value)
        {
            $value = Yii::app()->request->getParam($param);
        }
        return $value;
    }
    /**
    * Show users table
    */
    public function index()
    {
        if (!Permission::model()->hasGlobalPermission('users','read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect(array("admin/"));
        }

        App()->getClientScript()->registerPackage('jquery-tablesorter');
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'companies.js');

        $aData = array();
        // Page size
        if (Yii::app()->request->getParam('pageSize')) {
            Yii::app()->user->setState('pageSize', (int)Yii::app()->request->getParam('pageSize'));
        }
        $aData['pageSize']= Yii::app()->user->getState('pageSize', (int)Yii::app()->params['defaultPageSize']);

        $aData['title_bar']['title'] = gT('Company List');
        $aData['fullpagebar']['closebutton']['url'] = true;
        $model = new Company();
        $aData['model']=$model;
        $this->_renderWrappedTemplate('companies', 'editusers', $aData);
    }

    private function _getSurveyCountForUser(array $user)
    {
        return Survey::model()->countByAttributes(array('owner_id' => $user['uid']));
    }

    /**
     * Add a new survey administrator user
     *
     * @return void
     */
    public function adduser()
    {
        if (!Permission::model()->hasGlobalPermission('users','create')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect(array("admin/companies/sa/index"));
        }

        $name = flattenText(Yii::app()->request->getPost('name'), false, true);
        $aViewUrls = array();
        if (empty($name)) {
            $aViewUrls['message'] = array('title' => gT("Failed to add company"), 'message' => gT("A name was not supplied."), 'class'=> 'text-warning');
        }
        elseif (Company::model()->find("name=:name",array(':name'=>$name))) {
            // TODO: If error, we want to keep the form values. Can't do it nicely without CActiveForm?
            Yii::app()->setFlashMessage(gT("The company already exists."), 'error');
            $this->getController()->redirect(array('/admin/companies/sa/index'));
        }
        else
        {
            $varlist ="name,branch,address,contact,phone,email,website";
            $vars = array();
            foreach(explode(",",$varlist) as $var){
                $vars[$var] = $$var = flattenText(Yii::app()->request->getPost($var), false, true);
            }
            $vars['parent_id'] = Yii::app()->user->id;
            //echo "<pre>".print_r($vars,true)."</pre>"; die();
            Company::model()->insertUser($vars);

            Yii::app()->setFlashMessage(gT("Company added successfully."),'success');
            $this->getController()->redirect(array("admin/companies/sa/index"));
        }

        $this->_renderWrappedTemplate('user', $aViewUrls);
    }

    /**
    * Delete user
    */
    public function deluser()
    {

        if (!Permission::model()->hasGlobalPermission('superadmin','read') && !Permission::model()->hasGlobalPermission('users','delete')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect(array("admin/user/sa/index"));
        }

        $aViewUrls = array();

        $postuserid = $this->_getPostOrParam("uid");

        //If there was no uid transferred
        if (!$postuserid)
        {
            Yii::app()->setFlashMessage(gT("Could not delete user. User was not supplied."),'error');
            $this->getController()->redirect(array("admin/user/sa/index"));
            return;
        }

        //DELETE USER FROM TABLE
        $dresult = Company::model()->deleteUser($postuserid);

        Yii::app()->setFlashMessage(gT("Company deleted successfully."),'success');
        $this->getController()->redirect(array("admin/companies/sa/index"));
    }

    /**
    * Modify User
    */
    public function modifyuser()
    {

        if ( Yii::app()->request->getParam('uid') !=''  )
        {
            $postuserid = (int) Yii::app()->request->getParam("uid");
            $sresult = Company::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
            $sresultcount = count($sresult);


            if (Permission::model()->hasGlobalPermission('superadmin','read') || Yii::app()->session['loginID'] == $postuserid ||
            (Permission::model()->hasGlobalPermission('users','update') && $sresultcount > 0) )
            {
                $sresult = Company::model()->parentAndUser($postuserid);
                if(empty($sresult))
                {
                    Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
                    $this->getController()->redirect(array("admin/companies/sa/index"));
                }
                $aData = array();
                $aData['aUserData'] = $sresult;

                $aData['fullpagebar']['savebutton']['form'] = 'moduserform';
                // Close button, UrlReferrer;
                $aData['fullpagebar']['closebutton']['url_keep'] = true;
                $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer( Yii::app()->createUrl("admin/companies/sa/index") );

                $this->_renderWrappedTemplate('companies', 'modifyuser', $aData);
                return;
            }
            else
            {
                Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
                $this->getController()->redirect(array("admin/user/sa/index"));
            }
        }
        $this->getController()->redirect(array("admin/user/sa/index"));
    }

    /**
    * Modify User POST
    */
    public function moduser()
    {
        $postuserid = (int) Yii::app()->request->getPost("uid");
        $addsummary = '';
        $aViewUrls = array();

        $sresult = Company::model()->findAllByAttributes(array('uid' => $postuserid, 'parent_id' => Yii::app()->session['loginID']));
        $sresultcount = count($sresult);

        if ((Permission::model()->hasGlobalPermission('superadmin','read') || $postuserid == Yii::app()->session['loginID'] ||
        ($sresultcount > 0 && Permission::model()->hasGlobalPermission('users','update'))) && !(Yii::app()->getConfig("demoMode") == true && $postuserid == 1)
        )
        {
            $oRecord = Company::model()->findByPk($postuserid);
            $varlist ="name,branch,address,contact,phone,email,website";
            $vars = array();
            foreach(explode(",",$varlist) as $var){
                $oRecord->$var = flattenText(Yii::app()->request->getPost($var), false, true);
            }
            $uresult = $oRecord->save();    // store result of save in uresult
            if ($uresult) // When saved successfully
            {
                Yii::app()->setFlashMessage(gT("Successfully updated company!"),'success');
                $this->getController()->redirect(array("/admin/companies/sa/modifyuser/uid/".$postuserid));
            }
            else
            {
                //Saving the user failed for some reason, message about email is not helpful here
                // Username and/or email adress already exists.
                Yii::app()->setFlashMessage(  gT("Could not modify company data."),'error');
                $this->getController()->redirect(array("/admin/companies/sa/modifyuser/uid/".$postuserid));
            }
        }
        else
        {
            Yii::app()->setFlashMessage(  gT("Could not modify company data."),'error');
            $this->getController()->redirect(array("/admin/"));
        }

        $aData = array();
        $aData['fullpagebar']['continuebutton']['url'] = 'admin/companies/sa/index';
        $this->_renderWrappedTemplate('user', $aViewUrls, $aData);
    }


    public function savepermissions()
    {

        $iUserID=(int)App()->request->getPost('uid');
        // A user may not modify his own permissions
        if (Yii::app()->session['loginID']==$iUserID) {
            Yii::app()->setFlashMessage(gT("You are not allowed to edit your own user permissions."),"error");
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
        // Can not update initial superadmin permissions (with findByAttributes : found the first user without parent)
        $oInitialAdmin = User::model()->findByAttributes(array('parent_id' => 0));
        if ($oInitialAdmin && $oInitialAdmin->uid == $iUserID) // it's the original superadmin !!!
        {
            Yii::app()->setFlashMessage(gT("Initial Superadmin permissions cannot be updated!"),'error');
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
        $aBaseUserPermissions = Permission::model()->getGlobalBasePermissions();

        $aPermissions=array();
        foreach ($aBaseUserPermissions as $sPermissionKey=>$aCRUDPermissions)
        {
            foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue)
            {
                if (!in_array($sCRUDKey,array('create','read','update','delete','import','export'))) continue;

                if ($CRUDValue)
                {
                    if(isset($_POST["perm_{$sPermissionKey}_{$sCRUDKey}"])){
                        $aPermissions[$sPermissionKey][$sCRUDKey]=1;
                    }
                    else
                    {
                        $aPermissions[$sPermissionKey][$sCRUDKey]=0;
                    }
                }
            }
        }

        if (Permission::model()->setPermissions($iUserID, 0, 'global', $aPermissions))
        {
            Yii::app()->session['flashmessage'] = gT("Permissions were successfully updated.");
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
        else
        {
            Yii::app()->session['flashmessage'] = gT("There was a problem updating the user permissions.");
            $this->getController()->redirect(array("admin/user/sa/index"));
        }

    }

    public function setuserpermissions()
    {
        $iUserID = (int) Yii::app()->request->getPost('uid');

        // Can not update initial superadmin permissions (with findByAttributes : found the first user without parent)
        $oInitialAdmin = User::model()->findByAttributes(array('parent_id' => 0));

        if ($oInitialAdmin && $oInitialAdmin->uid == $iUserID) // Trying to update the original superadmin !!!
        {
            Yii::app()->setFlashMessage(gT("Initial Superadmin permissions cannot be updated!"),'error');
            $this->getController()->redirect(array("admin/user/sa/index"));
        }

        $aBaseUserPermissions = Permission::model()->getGlobalBasePermissions();
        if ($iUserID)
        {
            //Never update 1st admin
            if(Permission::model()->hasGlobalPermission('superadmin','read'))
                $oUser = User::model()->findByAttributes(array('uid' => $iUserID));
            else
                $oUser = User::model()->findByAttributes(array('uid' => $iUserID, 'parent_id' => Yii::app()->session['loginID']));
        }

        // Check permissions
        $aBasePermissions=Permission::model()->getGlobalBasePermissions();
        if (!Permission::model()->hasGlobalPermission('superadmin','read')) // if not superadmin filter the available permissions as no admin may give more permissions than he owns
        {
            Yii::app()->session['flashmessage'] = gT("Note: You can only give limited permissions to other users because your own permissions are limited, too.");
            $aFilteredPermissions=array();
            foreach  ($aBasePermissions as $PermissionName=>$aPermission)
            {
                foreach ($aPermission as $sPermissionKey=>&$sPermissionValue)
                {
                    if ($sPermissionKey!='title' && $sPermissionKey!='img' && !Permission::model()->hasGlobalPermission($PermissionName, $sPermissionKey)) $sPermissionValue=false;
                }
                // Only show a row for that permission if there is at least one permission he may give to other users
                if ($aPermission['create'] || $aPermission['read'] || $aPermission['update'] || $aPermission['delete'] || $aPermission['import'] || $aPermission['export'])
                {
                    $aFilteredPermissions[$PermissionName]=$aPermission;
                }
            }
            $aBasePermissions=$aFilteredPermissions;
        }

        if(isset($oUser))
        {
            if ( $oUser  && (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('users','update') &&  Yii::app()->session['loginID'] != $iUserID) )
            {
                // Only the original superadmin (UID 1) may create superadmins
                if (Yii::app()->session['loginID']!=1)
                {
                    unset($aBasePermissions['superadmin']);
                }

                $aData = array();
                $aData['aBasePermissions'] = $aBasePermissions;
                $aData['oUser'] = $oUser;

                App()->getClientScript()->registerPackage('jquery-tablesorter');
                $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'userpermissions.js');

                $aData['fullpagebar']['savebutton']['form'] = 'savepermissions';
                $aData['fullpagebar']['closebutton']['url_keep'] = true;
                $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer( Yii::app()->createUrl("admin/user/sa/index") );

                $this->_renderWrappedTemplate('user', 'setuserpermissions', $aData);
            }
            else
            {
                Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
                $this->getController()->redirect(array("admin/user/sa/index"));
            }
        }
        else
        {
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
    }

    public function setusertemplates()
    {
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'users.js');
        $postuserid = (int) Yii::app()->request->getPost("uid");
        $aData['postuser']  = flattenText(Yii::app()->request->getPost("user"));
        $aData['postemail'] = flattenText(Yii::app()->request->getPost("email"));
        $aData['postuserid'] = $postuserid;
        $aData['postfull_name'] = flattenText(Yii::app()->request->getPost("full_name"));
        $this->_refreshtemplates();
        $templaterights=array();
        foreach (getUserList() as $usr)
        {
            if ($usr['uid'] == $postuserid)
            {
                $trights = Permission::model()->findAllByAttributes(array('uid' => $usr['uid'],'entity'=>'template'));
                foreach ($trights as $srow)
                {
                    $templaterights[$srow["permission"]] = array("use"=>$srow["read_p"]);
                }
                $templates = Template::model()->findAll();
                $aData['data'] = array('templaterights'=>$templaterights,'templates'=>$templates);
            }
        }

        $aData['fullpagebar']['savebutton']['form'] = 'modtemplaterightsform';
        $aData['fullpagebar']['closebutton']['url_keep'] = true;
        $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer( Yii::app()->createUrl("admin/user/sa/index") );

        $this->_renderWrappedTemplate('user', 'setusertemplates', $aData);
    }

    public function usertemplates()
    {

        $postuserid = (int) Yii::app()->request->getPost('uid');

        // SUPERADMINS AND MANAGE_TEMPLATE USERS CAN SET THESE RIGHTS
        if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','update'))
        {
            $aTemplatePermissions = array();
            $tresult = Template::model()->findAll();
            foreach ($tresult as $trow)
            {
                if (isset($_POST[$trow["folder"] . "_use"]))
                    $aTemplatePermissions[$trow["folder"]] = $_POST[$trow["folder"] . "_use"];
            }
            foreach ($aTemplatePermissions as $key => $value)
            {
                $oPermission = Permission::model()->findByAttributes(array('permission' => $key, 'uid' => $postuserid, 'entity'=>'template'));
                if (empty($oPermission))
                {
                    $oPermission = new Permission;
                    $oPermission->uid = $postuserid;
                    $oPermission->permission = $key;
                    $oPermission->entity='template';
                    $oPermission->entity_id=0;
                }
                $oPermission->read_p = $value;
                $uresult = $oPermission->save();
            }
            if ($uresult !== false) {
                Yii::app()->setFlashMessage(gT("Template permissions were updated successfully."));
            }
            else
            {
                Yii::app()->setFlashMessage(gT("Error while updating template permissions."),'error');
            }
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
        else
        {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
    }

    /**
     * Manage user personal settings
     */
    public function personalsettings()
    {
        // Save Data
        if (Yii::app()->request->getPost("action")) {
            $aData = array(
            'lang' => Yii::app()->request->getPost('lang'),
            'dateformat' => Yii::app()->request->getPost('dateformat'),
            'htmleditormode' => Yii::app()->request->getPost('htmleditormode'),
            'questionselectormode' => Yii::app()->request->getPost('questionselectormode'),
            'templateeditormode' => Yii::app()->request->getPost('templateeditormode'),
            'full_name'=> Yii::app()->request->getPost('fullname'),
            'email'=> Yii::app()->request->getPost('email')
            );

            $error1 = false;
            if (Yii::app()->request->getPost('password')!='' && !Yii::app()->getConfig('demoMode'))
            {
                if (Yii::app()->request->getPost('password')==Yii::app()->request->getPost('repeatpassword'))
                {
                    $aData['password']=hash( "sha256",Yii::app()->request->getPost('password'));
                }
                else
                {
                    $error1= true;
                    Yii::app()->setFlashMessage(gT("Your new password was not saved because the passwords did not match."),'error');
                }
            }

            $uresult = User::model()->updateByPk(Yii::app()->session['loginID'], $aData);

            if (Yii::app()->request->getPost('lang')=='auto')
            {
                $sLanguage= getBrowserLanguage();
            }
            else
            {
                $sLanguage=Yii::app()->request->getPost('lang');
            }

            Yii::app()->session['adminlang'] = $sLanguage;
            Yii::app()->setLanguage($sLanguage);

            Yii::app()->session['htmleditormode'] = Yii::app()->request->getPost('htmleditormode');
            Yii::app()->session['questionselectormode'] = Yii::app()->request->getPost('questionselectormode');
            Yii::app()->session['templateeditormode'] = Yii::app()->request->getPost('templateeditormode');
            Yii::app()->session['dateformat'] = Yii::app()->request->getPost('dateformat');
            if($error1 == false){
              Yii::app()->setFlashMessage(gT("Your personal settings were successfully saved.")); 
            }

            if (Yii::app()->request->getPost("saveandclose")) {
                $this->getController()->redirect(array("admin/survey/sa/index"));
            }
        }
        else {
            $aData = array();
        }

        // Get user lang
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $aLanguageData=array('auto'=>gT("(Autodetect)"));
        foreach (getLanguageData(true, Yii::app()->session['adminlang']) as $langkey => $languagekind)
        {
           $aLanguageData[$langkey]=html_entity_decode($languagekind['nativedescription'].' - '.$languagekind['description'],ENT_COMPAT,'utf-8');
        }
        $aData['aLanguageData'] = $aLanguageData;
        $aData['sSavedLanguage'] = $user->lang;
        $aData['sUsername'] = $user->users_name;
        $aData['sFullname'] = $user->full_name;
        $aData['sEmailAdress'] = $user->email;

        $aData['fullpagebar']['savebutton']['form'] = 'personalsettings';
        $aData['fullpagebar']['saveandclosebutton']['form'] = 'personalsettings';
        $aData['fullpagebar']['closebutton']['url_keep'] = true;
        $aData['fullpagebar']['closebutton']['url'] = Yii::app()->request->getUrlReferrer( Yii::app()->createUrl("admin") );

        // Render personal settings view
        if (isset($_POST['saveandclose']))
        {
            $this->getController()->redirect(array("admin/user/sa/index"));
        }
        else
        {
            $this->_renderWrappedTemplate('user', 'personalsettings', $aData);
        }
    }

    private function _getUserNameFromUid($uid)
    {
        $uid = sanitize_int($uid);
        $result = User::model()->findByPk($uid);

        if (!empty($result)) {
            return $result->users_name;
        }
        else
        {
            return false;
        }
    }

    private function _refreshtemplates()
    {
        $template_a = getTemplateList();
        foreach ($template_a as $tp => $fullpath)
        {
            // check for each folder if there is already an entry in the database
            // if not create it with current user as creator (user with rights "create user" can assign template rights)
            $result = Template::model()->findByPk($tp);

            if (count($result) == 0) {
                $post = new Template;
                $post->folder = $tp;
                $post->creator = Yii::app()->session['loginID'];
                $post->save();
            }
        }
        return true;
    }

    private function escape($str)
    {
        if (is_string($str)) {
            $str = $this->escape_str($str);
        }
        elseif (is_bool($str))
        {
            $str = ($str === true) ? 1 : 0;
        }
        elseif (is_null($str))
        {
            $str = 'NULL';
        }

        return $str;
    }

    /**
     * @param string $str
     */
    private function escape_str($str, $like = FALSE)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val)
            {
                $str[$key] = $this->escape_str($val, $like);
            }

            return $str;
        }

        // Escape single quotes
        $str = str_replace("'", "''", $this->remove_invisible_characters($str));

        return $str;
    }

    /**
     * @param string $str
     */
    private function remove_invisible_characters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

    /**
     * @param string $classMsg
     */
    private function _messageBoxWithRedirect($title, $message, $classMsg, $extra = "", $url = "", $urlText = "", $hiddenVars = array(), $classMbTitle = "header ui-widget-header")
    {

        $url = (!empty($url)) ? $url : $this->getController()->createUrl('admin/user/index');
        $urlText = (!empty($urlText)) ? $urlText : gT("Continue");

        $aData = array();
        $aData['title'] = $title;
        $aData['message'] = $message;
        $aData['url'] = $url;
        $aData['urlText'] = $urlText;
        $aData['classMsg'] = $classMsg;
        $aData['classMbTitle'] = $classMbTitle;
        $aData['extra'] = $extra;
        $aData['hiddenVars'] = $hiddenVars;

        return $aData;
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'user', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    public function group($id) {
        $request = Yii::app()->request;
        $company = Company::model()->findByPk($id);
        if (isset($_REQUEST['company_group'])) {
            $groups = $request->getParam('groups');
            $groups = $groups ? json_encode($groups) : json_encode([]);
            $company->selected_groups = $groups;
            if ($company->save()) {
                echo json_encode(['status' => 1, "message" => 'Sueecee']);
            } else {
                echo json_encode(['status' => 0, "message" => '']);
            }
            die();
        }
        $selected_groups = json_decode($company->selected_groups);

        $groups = QuestionGroup::model()->findAll();
        $this->_renderWrappedTemplate('companies', 'editgroup', compact('groups', 'selected_groups', 'company'));
        die();
    }

}
