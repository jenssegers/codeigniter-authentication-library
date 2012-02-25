--
-- `autologin` database
--

CREATE TABLE IF NOT EXISTS `autologin` (
  `user` int(11) NOT NULL,
  `series` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`user`,`series`)
);