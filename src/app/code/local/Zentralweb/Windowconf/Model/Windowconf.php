<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 30.01.2018
 * Time: 17:25
 */

class Zentralweb_Windowconf_Model_Windowconf extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('windowconf/windowconf');
    }

    public function loadByQuouteItemId($id)
    {
        $this->load($id, 'quote_id');
        return $this;
    }
}