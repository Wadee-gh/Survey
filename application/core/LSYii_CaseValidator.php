<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

class LSYii_CaseValidator extends CValidator {

    public $type='lower';


    public function validateAttribute($object,$attribute){

        if ($this->type=='upper')
        {
            if (strtoupper($object->$attribute)==$object->$attribute){
                return;
            }
            else
            {
                $this->addError($object, $attribute, gT('Text needs to be uppercase.'));
                return;
            }
        }
        else // default to lowercase
        {
            if (strtolower($object->$attribute)==$object->$attribute){
                return;
            }
            else
            {
                $this->addError($object, $attribute, gT('Text needs to be lowercase.'));
                return;
            }
        }
        return;
    }

}