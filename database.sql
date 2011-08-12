--
-- `users` database
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activated` int(1) NOT NULL,
  `registered` int(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- `users_autologin` database
--

CREATE TABLE IF NOT EXISTS `users_autologin` (
  `user` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `used` int(15) NOT NULL,
  `ip` varchar(40) NOT NULL,
  PRIMARY KEY (`user`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
