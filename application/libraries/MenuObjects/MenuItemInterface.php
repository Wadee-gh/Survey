<?php

namespace ls\menu;

/**
 * Interface descriptions here: https://qc.md/Extra_menus_event
 */
interface MenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function getIconClass();
    public function isDivider();
    public function isSmallText();
}

