<?php

class Zentralweb_Windowconf_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->loadLayout();

        $this->renderLayout();
    }

    public function saveconfigAction()
    {
        $zw_configurator_jsondata = $this->getRequest()->getParam('zw-configurator-jsondata');

        if($zw_configurator_jsondata != '')
        {
            $windowconf = Mage::getModel('windowconf/windowconf');
            $windowconf->setJson($zw_configurator_jsondata);
            //$windowconf->setOptions($zw_configurator_jsondata);

            try
            {
                $windowconf->save();

            }catch(Exception $ex)
            {
                Mage::log("\n  Save Error in Windowconf :: \n" . $ex->getMessage(), null, 'windowconf.log');
            }

            $this->_redirect('windowconf/index/index/confid/'.$windowconf->getId());

        }
    }
}