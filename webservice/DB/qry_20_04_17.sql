CREATE TABLE `device_info` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `device_type` varchar(255) CHARACTER SET utf8 NOT NULL,
  `device_token` longtext CHARACTER SET utf8 NOT NULL,
  `device_id` longtext CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;