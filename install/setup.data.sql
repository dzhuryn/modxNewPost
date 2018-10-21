CREATE TABLE IF NOT EXISTS `{PREFIX}new_post_cities` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `city_ref` varchar(50) NOT NULL,
  `city` varchar(60) NOT NULL,
  `city_ru` varchar(65) NOT NULL,
  `update_status` int(1) unsigned NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `{PREFIX}new_post_departments` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `ref` varchar(50) NOT NULL,
  `city_ref` varchar(50) NOT NULL,
  `num` int(3) unsigned NOT NULL,
  `warehouse_type` varchar(20) NOT NULL,
  `warehouse_type_description` varchar(30) NOT NULL,
  `address` varchar(60) NOT NULL,
  `address_ru` varchar(60) NOT NULL,
  `max_weight_allowed` int(3) NOT NULL,
  `update_status` int(1) unsigned NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM;