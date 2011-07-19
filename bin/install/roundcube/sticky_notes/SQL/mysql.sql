CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL auto_increment,
  `nid` int(11) NOT NULL,
  `width` int(11) default '0',
  `height` int(11) default '0',
  `pos_x` int(11) default '0',
  `pos_y` int(11) default '0',
  `text` text,
  `user_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
