
DROP TABLE IF EXISTS `{dbprefix}guanjiaset`;

CREATE TABLE `{dbprefix}guanjiaset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setdata` text(0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


insert  into `{dbprefix}guanjiaset`(`id`,`setdata`) values (1,'{"guanjia_token":"guanjia.seowhy.com","titleUnique":"true"}');
