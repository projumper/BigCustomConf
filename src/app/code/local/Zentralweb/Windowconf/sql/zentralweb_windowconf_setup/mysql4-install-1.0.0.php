<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 30.01.2018
 * Time: 11:33
 */

echo 'Running This Upgrade: '.get_class($this)."\n <br /> \n";
//die("Exit for now");

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `window_conf_jsons` (
`conf_id` int(11) NOT NULL,
  `json` text,
  `quote_id` int(11) DEFAULT NULL,
  `options` text,
  `date` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

    
");
$installer->endSetup();