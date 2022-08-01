<?php
$this->_getAdminHeader(Yii::app()->session['metaHeader']);
$this->_showadminmenu();
$this->_getAdminFooter("http://qc.md", gT("QstConn online manual"));
?>