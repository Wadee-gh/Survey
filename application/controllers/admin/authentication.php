<?php

// see: https://scrutinizer-ci.com/g/QstConn/QstConn/issues/master/files/application/controllers/admin/authentication.php?selectedSeverities[0]=10&orderField=path&order=asc&honorSelectedPaths=0
// use ls\pluginmanager\PluginEvent;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * QstConn
 * Copyright (C) 2007-2011 The QstConn Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * QstConn is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Authentication Controller
 *
 * This controller performs authentication
 *
 * @package        QstConn
 * @subpackage    Backend
 */
class Authentication extends Survey_Common_Action {

    /**
     * Show login screen and parse login data
     * Will redirect or echo json depending on ajax call
     * This function is called while accessing the login page: index.php/admin/authentication/sa/login
     */
    public function index() {
        // The page should be shown only for non logged in users
        $this->_redirectIfLoggedIn();

        // Result can be success, fail or data for template
        $result = self::prepareLogin();

        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
        $succeeded = isset($result[0]) && $result[0] == 'success';
        $failed = isset($result[0]) && $result[0] == 'failed';

        // If Ajax, echo success or failure json
        if ($isAjax) {
            Yii::import('application.helpers.admin.ajax_helper', true);
            if ($succeeded) {
                ls\ajax\AjaxHelper::outputSuccess(gT('Successful login'));
                return;
            } else if ($failed) {
                ls\ajax\AjaxHelper::outputError(gT('Incorrect username and/or password!'));
                return;
            }
        }
        // If not ajax, redirect to admin startpage or again to login form
        else {
            if ($succeeded) {
                self::doRedirect();
            } else if ($failed) {
                $message = $result[1];
                App()->user->setFlash('error', $message);
                App()->getController()->redirect(array('/admin/authentication/sa/login'));
            }
        }

        // Neither success nor failure, meaning no form submission - result = template data from plugin
        $aData = $result;

        // If for any reason, the plugin bugs, we can't let the user with a blank screen.
        $this->_renderWrappedTemplate('authentication', 'login', $aData);
    }

    public function customRedirect($token = null) {
        if (Yii::app()->session['complete_by'] != $token) {
            $this->logout2();
        }
        //echo 'hello'; die();
        //echo "<pre>".print_r($_REQUEST,true)."</pre>"; die();
        // get cparticipation record.
        $vars = $_REQUEST;
        if (!isset($token))
            $token = $vars['token'];
        CParticipant::model()->clearPreviousSurvey($token);
        $cparticipation = CParticipant::model()->getByToken($token);
        //echo "<pre>".print_r($cparticipation,true)."</pre>"; die();
        if (empty($cparticipation)) {
            echo "Invalid survey link.";
            die();
        }

        // get status.
        $status = $cparticipation['status'];
        /*         * *
          status values:
          0 - new, load registration page.
          1 - submitted registration page, save participant, create token.
          2 - token created, load survey.
         * */
        if ($status == 0) {
            Yii::app()->clientScript->registerPackage('bootstrap-datetimepicker');
            $this->_renderWrappedTemplate('authentication', 'customregistration', compact('cparticipation'));
        } else
        if ($status == 1) {
            $token = $cparticipation['lime_token'];
            if (strlen($token) == 0) {
                $id = $cparticipation['id'];
                $result = Participant::model()->createCustomParticipant($id, $vars, $cparticipation['participant_id']);
                $cparticipation = CParticipant::model()->getById($id);
            }
            //$this->_renderWrappedTemplate('authentication', 'testsurvey', compact('cparticipation'));
            $survey_id = $cparticipation['survey_id'];
            $token = $cparticipation['lime_token'];
            killSurveySession($survey_id);
//            unset($_SESSION['survey_' . $survey_id]['LEMtokenResume']);
//            $_SESSION['survey_' . $survey_id]['step'] = 0;
            $this->getController()->redirect(array("/survey/index/sid/" . $survey_id . "/token/" . $token));
        }
    }

    public function customRegistration() {
        $vars = $_POST;
        if (!empty($vars)) {
            //echo "<pre>".print_r($vars,true)."</pre>"; die();
            $flist = "id,first_name,last_name,mrn_id,email,dob";
            $data = array();
            $vars['dob'] = date("Y-m-d", strtotime($vars['dob']));
            foreach (explode(",", $flist) as $field) {
                $data[$field] = $vars[$field];
            }
            $result = CParticipant::model()->updateRow($data);
            //echo "<pre>".print_r($result,true)."</pre>";
            if ($result['status'] == 'error') {
                die($result['message']);
            } else {
                $row = $result['row'];
                $id = $row->id;
                $result = Participant::model()->createCustomParticipant($id, $vars);
                //echo "<pre>".print_r($result,true)."</pre>"; //die();

                $cparticipation = CParticipant::model()->getById($id);
                $token = $cparticipation['token'];
                $this->customRedirect($token);
            }
        }
    }

    /**
     * Prepare login and return result
     * It checks if the authdb plugin is registered and active
     * @return array Either success, failure or plugin data (used in login form)
     */
    public static function prepareLogin() {
        $aData = array();

        // Plugins, include core plugins, can't be activated by default.
        // So after a fresh installation, core plugins are not activated
        // They need to be manually loaded.
        if (!class_exists('Authdb', false)) {
            $plugin = Plugin::model()->findByAttributes(array('name' => 'Authdb'));
            if (!$plugin) {
                $plugin = new Plugin();
                $plugin->name = 'Authdb';
                $plugin->active = 1;
                $plugin->save();
                App()->getPluginManager()->loadPlugin('Authdb', $plugin->id);
            } else {
                $plugin->active = 1;
                $plugin->save();
            }
        }

        // In Authdb, the plugin event "beforeLogin" checks if the url param "onepass" is set
        // if yes, it will call  AuthPluginBase::setAuthPlugin to set to true the plugin private parameter "_stop", so the form will not be displayed
        // @see: application/core/plugins/Authdb/Authdb.php: function beforeLogin()
        $beforeLogin = new PluginEvent('beforeLogin');
        $beforeLogin->set('identity', new LSUserIdentity('', ''));
        App()->getPluginManager()->dispatchEvent($beforeLogin);

        /* @var $identity LSUserIdentity */
        $identity = $beforeLogin->get('identity');                              // Why here?
        // If the plugin private parameter "_stop" is false and the login form has not been submitted: render the login form
        if (!$beforeLogin->isStopped() && is_null(App()->getRequest()->getPost('login_submit'))) {
            // First step: set the value of $aData['defaultAuth']
            // This variable will be used to select the default value of the Authentication method selector
            // which is shown only if there is more than one plugin auth on...
            // @see application/views/admin/authentication/login.php
            // First it checks if the current plugin force the authentication default value...
            // NB: A plugin SHOULD NOT be able to over pass the configuration file
            // @see: http://img.memecdn.com/knees-weak-arms-are-heavy_c_3011277.jpg
            if (!is_null($beforeLogin->get('default'))) {
                $aData['defaultAuth'] = $beforeLogin->get('default');
            } else {
                // THen, it checks if the the user set a different default plugin auth in application/config/config.php
                // eg: 'config'=>array()'debug'=>2,'debugsql'=>0, 'default_displayed_auth_method'=>'muh_auth_method')
                if (App()->getPluginManager()->isPluginActive(Yii::app()->getConfig('default_displayed_auth_method'))) {
                    $aData['defaultAuth'] = Yii::app()->getConfig('default_displayed_auth_method');
                } else {
                    $aData['defaultAuth'] = 'Authdb';
                }
            }

            // Call the plugin method newLoginForm
            // For Authdb:  @see: application/core/plugins/Authdb/Authdb.php: function newLoginForm()
            $newLoginForm = new PluginEvent('newLoginForm');
            App()->getPluginManager()->dispatchEvent($newLoginForm);            // inject the HTML of the form inside the private varibale "_content" of the plugin
            $aData['summary'] = self::getSummary('logout');
            $aData['pluginContent'] = $newLoginForm->getAllContent();           // Retreives the private varibale "_content" , and parse it to $aData['pluginContent'], which will be  rendered in application/views/admin/authentication/login.php
        } else {
            // The form has been submited, or the plugin has been stoped (so normally, the value of login/password are available)
            // Handle getting the post and populating the identity there
            $authMethod = App()->getRequest()->getPost('authMethod', $identity->plugin);      // If form has been submitted, $_POST['authMethod'] is set, else  $identity->plugin should be set, ELSE: TODO error
            $identity->plugin = $authMethod;

            // Call the function afterLoginFormSubmit of the plugin.
            // For Authdb, it calls AuthPluginBase::afterLoginFormSubmit()
            // which set the plugin's private variables _username and _password with the POST informations if it's a POST request else it does nothing
            $event = new PluginEvent('afterLoginFormSubmit');
            $event->set('identity', $identity);
            App()->getPluginManager()->dispatchEvent($event, array($authMethod));
            $identity = $event->get('identity');

            // Now authenticate
            // This call LSUserIdentity::authenticate() (application/core/LSUserIdentity.php))
            // which will call the plugin function newUserSession() (eg: Authdb::newUserSession() )
            // TODO: for sake of clarity, the plugin function should be renamed to authenticate().
            if ($identity->authenticate()) {
                FailedLoginAttempt::model()->deleteAttempts();
                App()->user->setState('plugin', $authMethod);

                // This call to AdminController::_GetSessionUserRights() ;
                // NB 1:calling another controller method from a controller method is a bad pratice
                // NB 2: this function only check if logged in user is super admin to set in session USER_RIGHT_INITIALSUPERADMIN
                // TODO: move this function to the user object
                Yii::app()->getController()->_GetSessionUserRights(Yii::app()->session['loginID']);
                Yii::app()->session['just_logged_in'] = true;
                Yii::app()->session['loginsummary'] = self::getSummary();

                $event = new PluginEvent('afterSuccessfulLogin');
                App()->getPluginManager()->dispatchEvent($event);
                LoginLogs::model()->loginSuccess();
                return array('success');
            } else {
                // Failed
                $event = new PluginEvent('afterFailedLoginAttempt');
                $event->set('identity', $identity);
                App()->getPluginManager()->dispatchEvent($event);

                $message = $identity->errorMessage;
                if (empty($message)) {
                    // If no message, return a default message
                    $message = gT('Incorrect username and/or password!');
                }
                return array('failed', $message);
            }
        }

        return $aData;
    }

    /**
     * Logout user
     * @return void
     */
    public function logout() {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        // Expire the CSRF cookie
        $cookie = new CHttpCookie('YII_CSRF_TOKEN', '');
        $cookie->expire = time() - 3600;
        Yii::app()->request->cookies['YII_CSRF_TOKEN'] = $cookie;
        App()->user->logout();
        App()->user->setFlash('loginmessage', gT('Logout successful.'));

        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);

        $this->getController()->redirect(array('/admin/authentication/sa/login'));
    }

    public function logout2() {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        // Expire the CSRF cookie
        $cookie = new CHttpCookie('YII_CSRF_TOKEN', '');
        $cookie->expire = time() - 3600;
        Yii::app()->request->cookies['YII_CSRF_TOKEN'] = $cookie;
        App()->user->logout();
        //App()->user->setFlash('loginmessage', gT('Logout successful.'));

        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);
    }

    /**
     * Forgot Password screen
     * @return void
     */
    public function forgotpassword() {
        $this->_redirectIfLoggedIn();

        if (!Yii::app()->request->getPost('action')) {
            $this->_renderWrappedTemplate('authentication', 'forgotpassword');
        } else {
            $sUserName = Yii::app()->request->getPost('user');
            $sEmailAddr = Yii::app()->request->getPost('email');

            $aFields = User::model()->findAllByAttributes(array('users_name' => $sUserName, 'email' => $sEmailAddr));

            // Preventing attacker from easily knowing whether the user and email address are valid or not (and slowing down brute force attacks)
            usleep(rand(Yii::app()->getConfig("minforgottenpasswordemaildelay"), Yii::app()->getConfig("maxforgottenpasswordemaildelay")));

            if (count($aFields) < 1 || ($aFields[0]['uid'] != 1 && !Permission::model()->hasGlobalPermission('auth_db', 'read', $aFields[0]['uid']))) {
                // Wrong or unknown username and/or email. For security reasons, we don't show a fail message
                $aData['message'] = '<br>' . gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you') . '<br>';
            } else {
                $aData['message'] = '<br>' . $this->_sendPasswordEmail($sEmailAddr, $aFields) . '</br>';
            }
            $this->_renderWrappedTemplate('authentication', 'message', $aData);
        }
    }

    /**
     * Send the forgot password email
     *
     * @param string $sEmailAddr
     * @param array $aFields
     */
    private function _sendPasswordEmail($sEmailAddr, $aFields) {
        $sFrom = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
        $sTo = $sEmailAddr;
        $sSubject = gT('User data');
        $sNewPass = createPassword();
        $sSiteName = Yii::app()->getConfig('sitename');
        $sSiteAdminBounce = Yii::app()->getConfig('siteadminbounce');

        $username = sprintf(gT('Username: %s'), $aFields[0]['users_name']);
        $password = sprintf(gT('New password: %s'), $sNewPass);

        $body = array();
        $body[] = sprintf(gT('Your user data for accessing %s'), Yii::app()->getConfig('sitename'));
        $body[] = $username;
        $body[] = $password;
        $body = implode("\n", $body);

        if (SendEmailMessage($body, $sSubject, $sTo, $sFrom, $sSiteName, false, $sSiteAdminBounce)) {
            User::model()->updatePassword($aFields[0]['uid'], $sNewPass);
            // For security reasons, we don't show a successful message
            $sMessage = gT('If username and email are valid and you are allowed to use internal database authentication a new password has been sent to you');
        } else {
            $sMessage = gT('Email failed');
        }

        return $sMessage;
    }

    /**
     * Get's the summary
     * @param string $sMethod login|logout
     * @param string $sSummary Default summary
     * @return string Summary
     */
    private static function getSummary($sMethod = 'login', $sSummary = '') {
        if (!empty($sSummary)) {
            return $sSummary;
        }

        switch ($sMethod) {
            case 'logout' :
                $sSummary = gT('Please log in first.');
                break;

            case 'login' :
            default :
                $sSummary = '<br />' . sprintf(gT('Welcome %s!'), Yii::app()->session['full_name']) . '<br />&nbsp;';
                if (!empty(Yii::app()->session['redirect_after_login']) && strpos(Yii::app()->session['redirect_after_login'], 'logout') === FALSE) {
                    Yii::app()->session['metaHeader'] = '<meta http-equiv="refresh"'
                            . ' content="1;URL=' . Yii::app()->session['redirect_after_login'] . '" />';
                    $sSummary = '<p><font size="1"><i>' . gT('Reloading screen. Please wait.') . '</i></font>';
                    unset(Yii::app()->session['redirect_after_login']);
                }
                break;
        }

        return $sSummary;
    }

    /**
     * Redirects a logged in user to the administration page
     */
    private function _redirectIfLoggedIn() {
        if (!Yii::app()->user->getIsGuest()) {
            $this->getController()->redirect(array('/admin'));
        }
    }

    /**
     * Check if a user can log in
     * @return bool|array
     */
    private function _userCanLogin() {
        $failed_login_attempts = FailedLoginAttempt::model();
        $failed_login_attempts->cleanOutOldAttempts();

        if ($failed_login_attempts->isLockedOut()) {
            return $this->_getAuthenticationFailedErrorMessage();
        } else {
            return true;
        }
    }

    /**
     * Redirect after login
     * @return void
     */
    private static function doRedirect() {
        $returnUrl = App()->user->getReturnUrl(array('/admin'));
        Yii::app()->getController()->redirect($returnUrl);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     * @return void
     */
    protected function _renderWrappedTemplate($sAction = 'authentication', $aViewUrls = array(), $aData = array()) {
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    public function register() {
        $model = new User();
        $model->scenario = "register";
        if (Yii::app()->request->getPost('action')) {
            $auth = Yii::app()->request->getParam('auth');
            if($auth==null){
                $users=Yii::app()->request->getPost('User');
                $companyName=explode("@", $users['email'])[0];
                $companyModel = Company::model()->find("name='$companyName'");
                if($companyModel){
                    Yii::app()->user->setFlash('error', "Email '".$users['email']."' already exists in our database");
                }else{
                    //Register Company
                    $iNewCompany=$this->_registerCompany();
                    //Register User
                    if($iNewCompany) {
                        $companyModel = Company::model()->findByPk($iNewCompany);
                        $this->_registerCompanyAsUser($companyModel, $model);
                    }else {
                        Yii::app()->user->setFlash('error', 'Company Not Created. Check details and try again.');
                    }
                }
            }else{
                $companyModel = Company::model()->find("signup_hash='$auth'");
                $this->_registerCompanyAsUser($companyModel, $model);
            }

        }
        $model->password = null;
        $this->_renderWrappedTemplate('authentication', 'register', compact('model'));
    }


    /**
     * Register a company as a user to enable login
     * @param $iNewCompany
     * @param $model
     */
    private function _registerCompanyAsUser($companyModel, $model)
    {
        if ($companyModel) {
            $parent_id = $companyModel->parent_id;
            $model->attributes = Yii::app()->request->getPost('User');
            $model->company_uid = $companyModel->uid;
            $model->parent_id = $parent_id;
            $model->users_name = $model->email;
            $model->full_name = explode("@", $model->email)[0];
            $transaction = Yii::app()->db->beginTransaction();
            if ($model->validate()) {
                try {
                    $model->password = hash('sha256', $model->password);
                    $model->save(false);
                    $iNewUID = $model->uid;
                    Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => Yii::app()->getConfig("defaulttemplate"), 'entity' => 'template', 'read_p' => 1, 'entity_id' => 0));
                    Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => 'auth_db', 'entity' => 'global', 'read_p' => 1, 'entity_id' => 0));
                    Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => 'surveys', 'entity' => 'global', 'read_p' => 1, 'entity_id' => 0));
                    Permission::model()->insertSomeRecords(array('uid' => $iNewUID, 'permission' => 'participantpanel', 'entity' => 'global', 'create_p' => 1, 'read_p' => 1, 'update_p' => 1, 'delete_p' => 1, 'export_p' => 1, 'entity_id' => 0));

                    Yii::app()->user->setFlash('success', 'Sign Up Successfully');
                    $transaction->commit();
                    $this->getController()->redirect(array("/admin/authentication/sa/login"));
                } catch (Exception $e) {
                    Yii::app()->user->setFlash('error', 'Please try again');
                    $transaction->rollback();
                }
            }
        } else {
            Yii::app()->user->setFlash('error', 'Not authorized for signup');
        }
    }

    private function _registerCompany(){
        $vars = array();
        $users=Yii::app()->request->getPost('User');
        $users['full_name']=explode("@", $users['email'])[0];
        $vars['name']=$users['full_name'];
        $vars['email']= $users['email'];
        $vars['parent_id'] = 1;
        $vars['selected_groups'] = null;
        return Company::model()->insertUser($vars);
    }
}
