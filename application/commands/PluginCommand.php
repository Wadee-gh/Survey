<?php
    /*
    * QstConn (tm)
    * Copyright (C) 2011 The QstConn Project Team / Carsten Schmitz
    * All rights reserved.
    * License: GNU/GPL License v2 or later, see LICENSE.php
    * QstConn is free software. This version may have been modified pursuant
    * to the GNU General Public License, and as distributed it includes or
    * is derivative of works licensed under the GNU General Public License or
    * other free or open source software licenses.
    * See COPYRIGHT.php for copyright notices and details.
    *
    */
    class PluginCommand extends CConsoleCommand
    {
        public $connection;

        public function actionCron($interval=null)
        {
            $pm = \Yii::app()->pluginManager;
            $event = new PluginEvent('cron');
            $event->set('interval', $interval);
            $pm->dispatchEvent($event);
        }
    }

?>
