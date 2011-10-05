<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
CREATE TABLE {$this->getTable('incentibox_coupons')}(
    unique_id int(11) NOT NULL auto_increment,
    incentibox_coupon_id int(11) NOT NULL DEFAULT 0,
    coupon_code varchar(32) NOT NULL DEFAULT '',
    coupon_amount decimal(8,4) NOT NULL DEFAULT '0.0000',
    order_minimum decimal(8,4) NOT NULL DEFAULT '0.0000',
    date_redeemed datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    emailed_to varchar(128) DEFAULT NULL,
    date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (unique_id),
    UNIQUE KEY (incentibox_coupon_id),
    UNIQUE KEY (coupon_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
?>