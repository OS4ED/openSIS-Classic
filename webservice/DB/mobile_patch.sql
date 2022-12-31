CREATE TABLE IF NOT EXISTS `temp_message_filepath_ws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyval` varchar(100) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `device_info` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `device_type` varchar(255) CHARACTER SET utf8 NOT NULL,
  `device_token` longtext CHARACTER SET utf8 NOT NULL,
  `device_id` longtext CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;