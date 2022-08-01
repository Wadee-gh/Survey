<?php

namespace ls\menu;

/**
 * Interface descriptions here: https://qc.md/Extra_menus_event
 */
interface MenuInterface
{
    public function isDropDown();
    public function getLabel();
    public function getHref();
    public function getMenuItems();
}

