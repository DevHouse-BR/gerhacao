CREATE TABLE IF NOT EXISTS `#__eventlist_events` (
`id` int(11) unsigned NOT NULL auto_increment,
`locid` int(11) unsigned NOT NULL default '0',
`catsid` int(11) unsigned NOT NULL default '0',
`dates` date NOT NULL default '0000-00-00',
`enddates` date NULL default NULL,
`times` time NULL default NULL,
`endtimes` time NULL default NULL,
`title` varchar(100) NOT NULL default '',
`alias` varchar(100) NOT NULL default '',
`created_by` int(11) unsigned NOT NULL default '0',
`modified` datetime NOT NULL,
`modified_by` int(11) unsigned NOT NULL default '0',
`author_ip` varchar(15) NOT NULL default '',
`created` datetime NOT NULL,
`datdescription` mediumtext NOT NULL,
`meta_keywords` varchar(200) NOT NULL default '',
`meta_description` varchar(255) NOT NULL default '',
`recurrence_number` int(2) NOT NULL default '0',
`recurrence_type` int(2) NOT NULL default '0',
`recurrence_counter` date NOT NULL default '0000-00-00',
`datimage` varchar(100) NOT NULL default '',
`checked_out` int(11) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
`registra` tinyint(1) NOT NULL default '0',
`unregistra` tinyint(1) NOT NULL default '0',
`published` tinyint(1) NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__eventlist_venues` (
`id` int(11) unsigned NOT NULL auto_increment,
`venue` varchar(50) NOT NULL default '',
`alias` varchar(100) NOT NULL default '',
`url` varchar(200)  NOT NULL default '',
`street` varchar(50) default NULL,
`plz` varchar(20) default NULL,
`city` varchar(50) default NULL,
`state` varchar(50) default NULL,
`country` varchar(2) default NULL,
`locdescription` mediumtext NOT NULL,
`meta_keywords` text NOT NULL,
`meta_description` text NOT NULL,
`locimage` varchar(100) NOT NULL default '',
`map` tinyint(4) NOT NULL default '0',
`created_by` int(11) unsigned NOT NULL default '0',
`author_ip` varchar(15) NOT NULL default '',
`created` datetime NOT NULL,
`modified` datetime NOT NULL,
`modified_by` int(11) unsigned NOT NULL default '0',
`published` tinyint(1) NOT NULL default '0',
`checked_out` int(11) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
`ordering` int(11) NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__eventlist_categories` (
`id` int(11) unsigned NOT NULL auto_increment,
`parent_id` int(11) unsigned NOT NULL default '0',
`catname` varchar(100) NOT NULL default '',
`alias` varchar(100) NOT NULL default '',
`catdescription` mediumtext NOT NULL,
`meta_keywords` text NOT NULL,
`meta_description` text NOT NULL,
`image` varchar(100) NOT NULL default '',
`published` tinyint(1) NOT NULL default '0',
`checked_out` int(11) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
`access` int(11) unsigned NOT NULL default '0',
`groupid` int(11) NOT NULL default '0',
`ordering` int(11) NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__eventlist_register` (
`id` int(11) unsigned NOT NULL auto_increment,
`event` int(11) NOT NULL default '0',
`uid` int(11) NOT NULL default '0',
`uregdate` varchar(50) NOT NULL default '',
`uip` varchar(15) NOT NULL default '',
PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__eventlist_groups` (
`id` int(11) unsigned NOT NULL auto_increment,
`name` varchar(150) NOT NULL default '',
`description` mediumtext NOT NULL,
`checked_out` int(11) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
PRIMARY KEY  (`id`)
) TYPE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

CREATE TABLE IF NOT EXISTS `#__eventlist_groupmembers` (
`group_id` INT( 11 ) NOT NULL DEFAULT '0',
`member` INT( 11 ) NOT NULL DEFAULT '0'
) TYPE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__eventlist_settings` (
  `id` int(11) NOT NULL,
  `oldevent` tinyint(4) NOT NULL,
  `minus` tinyint(4) NOT NULL,
  `showtime` tinyint(4) NOT NULL,
  `showtitle` tinyint(4) NOT NULL,
  `showlocate` tinyint(4) NOT NULL,
  `showcity` tinyint(4) NOT NULL,
  `showmapserv` tinyint(4) NOT NULL,
  `map24id` varchar(20) NOT NULL,
  `gmapkey` varchar(255) NOT NULL,
  `tablewidth` varchar(20) NOT NULL,
  `datewidth` varchar(20) NOT NULL,
  `titlewidth` varchar(20) NOT NULL,
  `locationwidth` varchar(20) NOT NULL,
  `citywidth` varchar(20) NOT NULL,
  `datename` varchar(100) NOT NULL,
  `titlename` varchar(100) NOT NULL,
  `locationname` varchar(100) NOT NULL,
  `cityname` varchar(100) NOT NULL,
  `formatdate` varchar(100) NOT NULL,
  `formattime` varchar(100) NOT NULL,
  `timename` varchar(50) NOT NULL,
  `showdetails` tinyint(4) NOT NULL,
  `showtimedetails` tinyint(4) NOT NULL,
  `showevdescription` tinyint(4) NOT NULL,
  `showdetailstitle` tinyint(4) NOT NULL,
  `showdetailsadress` tinyint(4) NOT NULL,
  `showlocdescription` tinyint(4) NOT NULL,
  `showlinkvenue` tinyint(4) NOT NULL,
  `showdetlinkvenue` tinyint(4) NOT NULL,
  `delivereventsyes` tinyint(4) NOT NULL,
  `mailinform` tinyint(4) NOT NULL,
  `mailinformrec` varchar(150) NOT NULL,
  `mailinformuser` tinyint(4) NOT NULL,
  `datdesclimit` varchar(15) NOT NULL,
  `autopubl` tinyint(4) NOT NULL,
  `deliverlocsyes` tinyint(4) NOT NULL,
  `autopublocate` tinyint(4) NOT NULL,
  `showcat` tinyint(4) NOT NULL,
  `catfrowidth` varchar(20) NOT NULL,
  `catfroname` varchar(100) NOT NULL,
  `evdelrec` tinyint(4) NOT NULL,
  `evpubrec` tinyint(4) NOT NULL,
  `locdelrec` tinyint(4) NOT NULL,
  `locpubrec` tinyint(4) NOT NULL,
  `sizelimit` varchar(20) NOT NULL,
  `imagehight` varchar(20) NOT NULL,
  `imagewidth` varchar(20) NOT NULL,
  `gddisabled` tinyint(4) NOT NULL,
  `imageenabled` tinyint(4) NOT NULL,
  `comunsolution` tinyint(4) NOT NULL,
  `comunoption` tinyint(4) NOT NULL,
  `catlinklist` tinyint(4) NOT NULL,
  `showfroregistra` tinyint(4) NOT NULL,
  `showfrounregistra` tinyint(4) NOT NULL,
  `eventedit` tinyint(4) NOT NULL,
  `eventeditrec` tinyint(4) NOT NULL,
  `eventowner` tinyint(4) NOT NULL,
  `venueedit` tinyint(4) NOT NULL,
  `venueeditrec` tinyint(4) NOT NULL,
  `venueowner` tinyint(4) NOT NULL,
  `lightbox` tinyint(4) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `showstate` tinyint(4) NOT NULL,
  `statename` varchar(100) NOT NULL,
  `statewidth` varchar(20) NOT NULL,
  `regname` tinyint(4) NOT NULL,
  `storeip` tinyint(4) NOT NULL,
  `commentsystem` tinyint(4) NOT NULL,
  `lastupdate` varchar(20) NOT NULL default '',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) TYPE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `#__eventlist_settings` VALUES (1, 0, 1, 0, 1, 1, 1, 0, '', '', '100%', '15%', '25%', '20%', '20%', 'Date', 'Title', 'Venue', 'City', '%d.%m.%Y', '%H.%M', 'h', 1, 0, 1, 1, 1, 1, 1, 2, -2, 0, 'example@example.com', 0, '1000', -2, -2, -2, 1, '20%', 'Type', 1, 1, 1, 1, '100', '100', '100', 0, 1, 0, 0, 1, 2, 2, -2, 1, 0, -2, 1, 0, 0, '[title], [a_name], [catsid], [times]', 'The event titled [title] starts on [dates]!', 0, 'State', 0, '', 1, 0, '1174491851', '', '');