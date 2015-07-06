--
-- Triggers `users`
--
DROP TRIGGER IF EXISTS `initialize_notifications`;
DELIMITER //
CREATE TRIGGER `initialize_notifications` AFTER INSERT ON `users`
 FOR EACH ROW INSERT INTO notifications (user_id, target, count) VALUES (NEW.id, "newsfeed", 0), (NEW.id, "files", 0), (NEW.id, "posts", 0)
//
DELIMITER ;

--
-- Triggers `posts`
--
DROP TRIGGER IF EXISTS `increment_notifications_posts`;
DELIMITER //
CREATE TRIGGER `increment_notifications_posts` AFTER INSERT ON `posts`
 FOR EACH ROW UPDATE notifications SET count = count+1 

WHERE notifications.user_id != NEW.user_id 
AND   notifications.target = "posts"
//
DELIMITER ;

--
-- Triggers `newsfeed`
--
DROP TRIGGER IF EXISTS `increment_notifications_newsfeed`;
DELIMITER //
CREATE TRIGGER `increment_notifications_newsfeed` AFTER INSERT ON `newsfeed`
 FOR EACH ROW UPDATE notifications SET count = count+1 

WHERE notifications.user_id != NEW.user_id 
AND   notifications.target = "newsfeed"
//
DELIMITER ;

--
-- Triggers `files`
--
DROP TRIGGER IF EXISTS `increment_notifications_files`;
DELIMITER //
CREATE TRIGGER `increment_notifications_files` AFTER INSERT ON `files`
 FOR EACH ROW UPDATE notifications SET count = count+1 

WHERE notifications.user_id != NEW.user_id 
AND   notifications.target = "files"
//
DELIMITER ;

