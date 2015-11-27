--
-- Table structure for table `users`
--

CREATE TABLE `users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `session_id` varchar(48) DEFAULT NULL,
 `cookie_token` varchar(128) DEFAULT NULL,
 `name` varchar(48) NOT NULL,
 `role` varchar(16) NOT NULL DEFAULT 'user',
 `hashed_password` varchar(128) NOT NULL,
 `email` varchar(64) NOT NULL,
 `is_email_activated` tinyint(1) NOT NULL DEFAULT '0',
 `email_token` varchar(48) DEFAULT NULL,
 `email_last_verification` int(11) DEFAULT NULL COMMENT 'unix timestamp',
 `pending_email` varchar(64) DEFAULT NULL COMMENT 'temporary email that will be used when user updates his current one',
 `pending_email_token` varchar(48) DEFAULT NULL,
 `profile_picture` varchar(48) NOT NULL DEFAULT 'default.png' COMMENT 'The base name for the image. Its not always unique because of default.jpg',
 PRIMARY KEY (`id`),
 UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `failed_logins`
--

CREATE TABLE `failed_logins` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_email` varchar(64) NOT NULL COMMENT 'It doesnt reference email in table users, this will prevent even unregistered users as well',
 `last_failed_login` int(11) DEFAULT NULL COMMENT 'unix timestamp of last failed login',
 `failed_login_attempts` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `ip_failed_logins`
--

CREATE TABLE `ip_failed_logins` (
 `ip` varchar(48) NOT NULL,
 `user_email` varchar(64) NOT NULL COMMENT 'It doesnt reference email in table users',
 PRIMARY KEY (`ip`,`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
 `ip` varchar(48) NOT NULL,
 PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `forgotten_passwords`
--

CREATE TABLE `forgotten_passwords` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `password_token` varchar(48) DEFAULT NULL,
 `password_last_reset` int(11) DEFAULT NULL COMMENT 'unix timestamp of last password reset request',
 `forgotten_password_attempts` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `forgotten_passwords_user` (`user_id`),
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `title` varchar(128) NOT NULL,
 `content` text NOT NULL,
 `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `post_id` int(11) NOT NULL,
 `content` varchar(512) NOT NULL,
 `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `newsfeed`
--

CREATE TABLE `newsfeed` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `content` varchar(512) NOT NULL,
 `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `filename` varchar(48) NOT NULL COMMENT 'original file name',
 `hashed_filename` varchar(48) NOT NULL COMMENT 'The hashed file name generated from hash(filename . extension)',
 `extension` varchar(8) NOT NULL COMMENT 'The file extension',
 `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `hashed_filename` (`hashed_filename`),
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
 `user_id` int(11) NOT NULL,
 `target` varchar(16) NOT NULL COMMENT 'Represents the target of the notification, like files, posts, ...etc',
 `count` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`user_id`,`target`),
 FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `todo`
--

CREATE TABLE `todo` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `user_id` int(11) NOT NULL,
     `content` varchar(512) NOT NULL,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;