<?php

class Zentralweb_Windowconf_Block_Windowconf extends Mage_Catalog_Block_Product_Abstract
{

    protected function _construct()
    {

        parent::_construct();
        $this->setTemplate('windowconf/window.phtml');
    }

    public function testBlock()
    {
        return "it works!";
    }

    public function getMegaOptions()
    {
        $conf_tree = array();
        $conf_tree1 = array();

        $to_sort_right = array();
        $right_sorted = array();

        $window_price_matrix_json = "";
        $window_price_matrix_block = Mage::getBlockSingleton('Zentralweb_Windowpricematrix_Block_Data');

        $product_model = Mage::getModel('catalog/product');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        //tables
        $catalog_product_option = $resource->getTableName('catalog_product_option');
        $catalog_product_option_title = $resource->getTableName('catalog_product_option_title');
        $catalog_product_option_type_value = $resource->getTableName('catalog_product_option_type_value');
        $catalog_product_option_type_price = $resource->getTableName('catalog_product_option_type_price');
        $catalog_product_option_type_title= $resource->getTableName('catalog_product_option_type_title');
        $mageworx_custom_options_option_description = $resource->getTableName('mageworx_custom_options_option_description');
        $mageworx_custom_options_option_type_image = $resource->getTableName('mageworx_custom_options_option_type_image');


        $all_main_product_options = $readConnection->fetchAll(
            'SELECT * 
            FROM '.$catalog_product_option.' 
            WHERE product_id= 414'
        );

        $i=0;

        foreach ($all_main_product_options as $main_product_option)
        {
            $all_product_option_values = $readConnection->fetchAll(
                'SELECT * FROM '. $catalog_product_option_type_value.' 
                LEFT JOIN `catalog_product_option_type_price` 
                ON (catalog_product_option_type_value.option_type_id=catalog_product_option_type_price.option_type_id) 
                WHERE option_id = '.$main_product_option['option_id']
            );

            if(count($all_product_option_values)>0)
            {

                foreach ($all_product_option_values as $product_option_type_value) {
                    
                    $conf_tree1[$i][$main_product_option['sort_order']]['dependent_ids'][$product_option_type_value['in_group_id']]=$product_option_type_value['dependent_ids'];
                    $conf_tree1[$i][$main_product_option['sort_order']]['option_id'] = $main_product_option['option_id'];

                    $conf_tree1[$i][$main_product_option['sort_order']]['current_sort_order'] = $main_product_option['sort_order'];

                    $conf_tree1[$i][$main_product_option['sort_order']]['title'] = $readConnection->fetchOne(
                        'SELECT title 
                         FROM '.$catalog_product_option_title.' 
                         WHERE option_id='.$main_product_option['option_id']
                    );


                    $conf_tree[$product_option_type_value['in_group_id']]['option_id'] = $main_product_option;
                    
                    //add price data
                    $conf_tree[$product_option_type_value['in_group_id']]['option_id']['store_id'] = isset($product_option_type_value['store_id']) ? $product_option_type_value['store_id'] : '';
                    $conf_tree[$product_option_type_value['in_group_id']]['option_id']['price'] = isset($product_option_type_value['price']) ? $product_option_type_value['price'] : '';
                    $conf_tree[$product_option_type_value['in_group_id']]['option_id']['price_type'] = isset($product_option_type_value['price_type']) ? $product_option_type_value['price_type'] : '';
                    
                    $conf_tree[$product_option_type_value['in_group_id']]['box_title'] = $readConnection->fetchOne('
                        SELECT title 
                        FROM '.$catalog_product_option_title.' 
                        WHERE option_id='.$main_product_option['option_id']
                    );

                    $conf_tree[$product_option_type_value['in_group_id']]['box_text'] = 'Box TEXT';

                    $conf_tree[$product_option_type_value['in_group_id']]['option_type_id'] = $product_option_type_value['option_type_id'];
                    $conf_tree[$product_option_type_value['in_group_id']]['option_id_id'] = $product_option_type_value['option_id'];

                    $image = $readConnection->fetchOne(
                        'SELECT image_file 
                         FROM '. $mageworx_custom_options_option_type_image .' 
                         WHERE option_type_id='.$product_option_type_value['option_type_id']
                    );

                    $conf_tree[$product_option_type_value['in_group_id']]['icon'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customoptions/'.$image;

                    if($desc = $readConnection->fetchOne('SELECT description FROM '.$mageworx_custom_options_option_description. ' WHERE option_id='.$main_product_option['option_id']))
                    {
                        $conf_tree[$product_option_type_value['in_group_id']]['box_description'] = $desc;

                    }else{

                        $conf_tree[$product_option_type_value['in_group_id']]['box_description'] = "";
                    }

                    if($product_option_type_value['sku'] != "")
                    {
                        $product = $product_model->loadByAttribute('sku',$product_option_type_value['sku']);

                        $conf_tree[$product_option_type_value['in_group_id']]['base_price_json'] = $this->getWindowPriceMatrix($product_option_type_value['sku']);

                        $conf_tree[$product_option_type_value['in_group_id']]['base_price_json_sku'] = $product_option_type_value['sku'];

                        //modified by Ivan2 for more performance and potomuchto tak lutshe! ;)
                        $product_data = $product ? $product->getData() : array();

                        $conf_tree[$product_option_type_value['in_group_id']]['icon_label'] = $product_data['thumbnail_label'];
                        
                        unset($product_data['stock_item']);

                        $conf_tree[$product_option_type_value['in_group_id']]['zwproduct'] = $product_data;

                    } else {

                        $conf_tree[$product_option_type_value['in_group_id']]['icon_label'] = '';
                    }

                    $conf_tree[$product_option_type_value['in_group_id']]['option_type_title'] = $readConnection->fetchOne('SELECT title FROM '.$catalog_product_option_type_title.' WHERE option_type_id='.$product_option_type_value['option_type_id']);

                    $conf_tree[$product_option_type_value['in_group_id']]['in_group_id'] = $product_option_type_value['in_group_id'];

                    $conf_tree[$product_option_type_value['in_group_id']]['dependent_ids'] = $product_option_type_value['dependent_ids'];
                }

                $i++;
            }
        }

        for($i=0; $i<count($conf_tree1); $i++)
        {

            foreach ($conf_tree1[$i] as $key_current_sort_order => $dependent_ids)
            {

                foreach ($dependent_ids['dependent_ids'] as $key_group_in => $dependet_id_string)
                {
                    $tmp_arr = preg_split('/,/', $dependet_id_string);

                    foreach ($tmp_arr as $dependet_id)
                    {

                        $next_sort_order = key($conf_tree1[$i+1]);

                        if($conf_tree[$dependet_id]['option_id']['sort_order'] != $next_sort_order)
                        {
                            $to_sort_right[$conf_tree[$dependet_id]['option_id']['sort_order']][$key_group_in] .= $dependet_id.',';

                        }else{

                            $right_sorted[$key_current_sort_order][$key_group_in] .= $dependet_id.',';
                        }
                    }
                }
            }
        }


        for($i=0; $i< count($conf_tree1); $i++)
        {
            $sort_order = array_keys($conf_tree1[$i]);

                foreach ($right_sorted[$sort_order[0]] as $key => $dependets_string) {
                    $tmp1_arr[$key] = substr($dependets_string, 0, strlen($dependets_string) - 1);
                }

                unset($conf_tree1[$i][$sort_order[0]]['dependent_ids']);
                $conf_tree1[$i][$sort_order[0]]['dependent_ids'] = $tmp1_arr ? $tmp1_arr : array();
                unset($tmp1_arr);
        }

        for($i=0; $i< count($conf_tree1); $i++) {

            $sort_order = array_keys($conf_tree1[$i]);

                foreach ($to_sort_right[$sort_order[0]] as $key => $dependets_string) {

                    $tmp2_arr[$key] = substr($dependets_string, 0, strlen($dependets_string) - 1);

                    if (array_key_exists($key, $conf_tree1[$i][$sort_order[0]]['dependent_ids'])) {
                        $conf_tree1[$i][$sort_order[0]]['dependent_ids'][$key] .= $tmp2_arr[$key];

                    } else {
                        $conf_tree1[$i][$sort_order[0]]['dependent_ids'][$key] = $tmp2_arr[$key];
                    }
                }
        }        
        
        /////add flag for show Height*Width screen
        $show_hw_screen_priorities = array(3020, 3021, 3120, 3121, 3220, 3320);
        
        if( sizeof($conf_tree1) ){
            foreach($conf_tree1 as $conf_tree1_key => $conf_tree1_item){


                $sort_order = array_keys($conf_tree1_item)[0];

                    if (in_array($sort_order, $show_hw_screen_priorities)) {
                        $conf_tree1[$conf_tree1_key][$sort_order]['next_hw_screen'] = 1;
                    } else {
                        $conf_tree1[$conf_tree1_key][$sort_order]['next_hw_screen'] = '';
                    }
            }
        }

        /////add static first step
        $static_step = array(
            '0' => array(
                'dependent_ids' => array(
                    0 => '393211,393212,393213'
                ),
                'option_id' => '',
                'next_sort_order' => '',
                'prev_sort_order' => '',
                'current_sort_order' => 0,
                'title' => '',
                'next_hw_screen' => ''
            )
        );
        
        $conf_tree1 = array_merge($static_step, $conf_tree1);

       // print '<div>';
       // print_r($conf_tree1);
       // print_r($conf_tree);
        

        //create file
        $js_text = 'var winconfJSONObject = ' . json_encode($conf_tree1) . ";\n";
        $js_text .= 'var winconfJSONItemsObject = ' . json_encode($conf_tree) . ";";
        $js_file = fopen( Mage::getBaseDir() . '/js/windowconf/global-json.js', 'w' ) or die("Unable to open file!");
        fwrite($js_file, $js_text);
        fclose($js_file);

    }

    private function getWindowPriceMatrix($sku)
    {
        if ($sku != "") {
            
            $base_price_file = $sku;//.'.csv';
            //$base_price_file = 'f-ks5-dk-3v.csv';

            $baseDir = Mage::getBaseDir();

            $dataLines = array();

            if (($handle = fopen($baseDir . DS . 'media/windowpricematrix' . DS . $base_price_file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

                    $dataLines[] = $data;
                }

                fclose($handle);

            } else {
                echo 'where is my file?';
            }

            //$priceMatrix = array('dataLines'=>$dataLines, 'options'=>$optionsArray);
            $priceMatrix = array('dataLines'=>$dataLines);

            if ($dataLines) {
                return json_encode($priceMatrix);
            }

        } else {

            return 'no File';

        }

    }


    public function checkId()
    {
        $conf_id = $this->getRequest()->getParam('confid');

        return $conf_id;
    }


    public function getConfJson($confid)
    {
        $windowconf = Mage::getModel('windowconf/windowconf');
        $windowconf->load($confid);

        if($json = $windowconf->getJson())
        {
            return $json;
        }else{
            return false;
        }

    }

}