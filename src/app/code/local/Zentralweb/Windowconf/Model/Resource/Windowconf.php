<?php


class Zentralweb_Windowconf_Model_Resource_Windowconf extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('windowconf/windowconf', 'conf_id');
    }
}