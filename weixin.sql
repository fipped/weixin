-- 表的结构 `user`
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weixin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT '还没有昵称',
  `head` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `geqian` varchar(255) COLLATE utf8_unicode_ci DEFAULT ':D 我很懒，什么都不想说' COMMENT '个性签名',
  `sex` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'girl',
  PRIMARY KEY (`id`),
  UNIQUE KEY `weixin` (`weixin`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 表的结构 `circle`
DROP TABLE IF EXISTS `circle`;
CREATE TABLE IF NOT EXISTS `circle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `saying` varchar(140) NOT NULL,
  `pics` varchar(3000) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`) ,
  FOREIGN KEY (u_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 表的结构 `chatlog
DROP TABLE IF EXISTS `chatlog`;
CREATE TABLE `chatlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `content` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (u_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (f_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 表的结构 `friend`
DROP TABLE IF EXISTS `friend`;
CREATE TABLE IF NOT EXISTS `friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `f_id` int(11) NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'request',
  PRIMARY KEY (`id`) ,
  FOREIGN KEY (u_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (f_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- 表的结构 `like`
DROP TABLE IF EXISTS `like`;
CREATE TABLE IF NOT EXISTS `like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `c_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) ,
  FOREIGN KEY (u_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (c_id) REFERENCES circle(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 表的结构 `comment`
DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL,
  `c_id` int(11) NOT NULL,
  `saying` varchar(140) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`) ,
  FOREIGN KEY (u_id) REFERENCES user(id) ON DELETE CASCADE,
  FOREIGN KEY (c_id) REFERENCES circle(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
