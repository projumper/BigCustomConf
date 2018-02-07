<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 22.01.2018
 * Time: 11:12
 */

class Zentralweb_Windowconf_Model_Observer extends Varien_Event_Observer
{
    public function __construct()
    {

    }

    public function sales_quote_add_item($observer)
    {
        $event = $observer->getEvent();

        $quote_item = $event->getQuoteItem();

        $params = Mage::app()->getRequest()->getParams();

        //print '<pre>';
        //print_r ($params);
        //die();

        $jsonData = json_decode($params['zw-configurator-jsondata']);

        //print '<pre>';
        //print_r($jsonData);
        //die();

        if ($jsonData->width && $jsonData->height && is_object($jsonData->options)) {
            //zw_width breite
            //zw_height hoeo
            $new_price = $this->_calculateNewPrice($params['product'], $jsonData->options, $jsonData->width, $jsonData->height);

        }


        if ($new_price) {

            $quote_item->setOriginalCustomPrice($new_price);

            $quote_item->setName('Individual conf. Fenster ' ." " . $params['zw_width'] . " xxxxxxxx " . $params['zw_height']);

            $windowconf = Mage::getModel('windowconf/windowconf');

            $quote_item->save();

            $confid = $params['confid_hidden'];

            if($confid > 0)
            {
                $windowconf->load($confid);
                $windowconf->setQuote_id($quote_item->getId());
                $windowconf->setOptions(serialize($jsonData->options));
                $windowconf->save();
            }else{
                $windowconf->setJson($params['zw-configurator-jsondata']);
                $windowconf->setOptions(serialize($jsonData->options));
                $windowconf->setQuote_id($quote_item->getId());
                $windowconf->save();
            }

            //print '<pre>';
            //print_r($windowconf);
            //die();
        }

        return $this;

    }

    private function _calculateNewPrice($product_id, $superAttribiutesOrdered, $length, $width)
    {
        $price = 0;

        $product_id = 414;

        if (is_object($superAttribiutesOrdered)) {

            $product = Mage::getModel('catalog/product')->load($product_id);

            print '<pre>';
            //print_r($product);

            //original price
            //$price += $this->_getBasePriceFromFile($product_id, $length, $width);
            //$pprice['orig_price'][]  = $this->_getBasePriceFromFile($product_id, $length, $width);
            //$pprice['orig_price']['w'] = $length;
            //$pprice['orig_price']['l'] = $width;
            //$pprice['total'][] = $price;

            foreach ($superAttribiutesOrdered as $key => $value_index) {

                //$option = $product->getOptionById($key);
                $option = $product->getOptionById($value_index->option_id);

                //print '<pre>';
                //print_r($option->zwGetValuesById($value_index->option_type_id));

                //whne the option is not neccesery for price calculation
                if(!is_object($option->zwGetValuesById($value_index->option_type_id))){
                    $pprice[$key]['value_index'] = $value_index->option_type_id;
                    continue;
                }

                if($value_index->sku){

                    //for($i=0; $i < count($value_index); $i++){

                        //$option_sku = $option->zwGetValuesById($value_index->option_type_id)->getSku();

                        $option_product_id = Mage::getSingleton('catalog/product')->getIdBySku($value_index->sku);

                        //FehlerBehandlung
                        $pprice['by_dim_sku'][] = $this->_getBasePriceFromFile($option_product_id, $length, $width);
                        $pprice['total'][] = $price;
                        $price += $this->_getBasePriceFromFile($option_product_id, $length, $width);

                    //}

                }else{

                    $priceType = $option->zwGetPriceType($value_index->option_type_id);

                    if ($priceType == 'percent') {

                        $price+= $option->zwGetValuesById($value_index->option_type_id)->getPrice() / 100 * $this->_getBasePriceFromFile($product_id, $length, $width);
                        $pprice['percent'][] = $option->zwGetValuesById($value_index->option_type_id)->getPrice() / 100 * $this->_getBasePriceFromFile($product_id, $length, $width);
                        $pprice['percent']['percent_value'][]= $option->zwGetValuesById($value_index->option_type_id)->getPrice();
                        $pprice['percent']['price_value'][]= $this->_getBasePriceFromFile($product_id, $length, $width);
                        $pprice['total'][] = $price;

                    } elseif ( ($option_sku = $option->zwGetValuesById($value_index->option_type_id)->getSku()) && ($priceType == 'fixed')) {

                        $option_product_id = Mage::getSingleton('catalog/product')->getIdBySku($option_sku);

                        //FehlerBehandlung
                        $price += $this->_getBasePriceFromFile($option_product_id, $length, $width);
                        $pprice['by_sku'][] = $this->_getBasePriceFromFile($option_product_id, $length, $width);
                        $pprice['by_sku']['p_id'] = $option_product_id;
                        $pprice['by_sku']['p_sku'] = $option_sku;
                        $pprice['total'][] = $price;

                    } else {

                        $price += $option->zwGetValuesById($value_index->option_type_id)->getPrice();
                        $pprice['fixed'][] = $option->zwGetValuesById($value_index->option_type_id)->getPrice();
                        $pprice['total'][] = $price;

                    }
                }
            }
            echo '<pre>';
            print_r($superAttribiutesOrdered);
            print_r($pprice);
            //die();
        }

        return $price;
    }


    public function sales_quote_item_set_product(Varien_Event_Observer $observer)
    {

        /* @var $item Mage_Sales_Model_Quote_Item */
        $item = $observer->getQuoteItem();

        $infoBuyRequest = $item->getbuyRequest();

        $item->setName('Individual conf. Fenster ' . 'Masse: ' . $infoBuyRequest->getData('zw_width') . ' x ' . $infoBuyRequest->getData('zw_height'));

        return $this;

    }


    public function checkout_cart_product_add_after(Varien_Event_Observer $observer)
    {
        Mage::Log("checkout_Cart_Product_Update_After");

        $params = Mage::app()->getRequest()->getParams();

        $zwItem = $observer->getEvent()->getQuoteItem();

        $zw_length = $params['zw_width'];

        $zw_height = $params['zw_height'];

        $zwItem->setName('Individual conf. Fenster '  . " " . $zw_height . " x " . $zw_length);

        $windowconf = Mage::getModel('windowconf/windowconf');
        $options = $windowconf->loadByQuouteItemId($zwItem->getId())->getOptions();
        $zwItem->setDescription($options);

    }

    /**
     *
     * get the base price depeneds from parameters
     *breite hoehe
     * @param $product_id
     * @return bool
     */
    private function _getBasePriceFromFile($product_id, $width, $length)
    {

        $product = Mage::getModel('catalog/product')->load($product_id);

        $base_price_file = $product->getData('zw_windowmatrixprice_baseprice');

        $baseDir = Mage::getBaseDir();

        $dataLines = array();

        if (($handle = fopen($baseDir . DS . 'media/windowpricematrix' . DS . $base_price_file, "r")) !== FALSE) {

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

                $dataLines[] = $data;

            }

            fclose($handle);

        } else {

            echo 'where is my file?';

            return false;
        }

        $lastArray = end($dataLines);

        $x = $y = 0;

        for ($i = 0; $i < count($dataLines[0]); $i++) {
            if ($dataLines[0][$i] == $width) {
                $x = $i;
            } elseif(($i < count($dataLines[0])-1) && ($dataLines[0][$i+1] > $width) && ($width > $dataLines[0][$i])) {
                $x = $i+1;
            }
        }

        $flag = false;

        for ($l = 1; $l < count($dataLines); $l++) {
            if ($dataLines[$l][0] == $length) {
                $y = $l;
                $flag = true;
            } elseif(($l < count($dataLines)-1) && ($dataLines[$l+1][0] > $length) && ($length > $dataLines[$l][0])) {
                $y = $l+1;
                $flag = true;
            }
            if($flag){
                //hmmm, i am happy that flag is false
            }
        }

        $price = $dataLines[$y][$x];
        return $price;
    }
}