--
-- Add initial admin and normal user
--

INSERT INTO `miniphp_68dozftn`.`users` (`id`, `session_id`, `cookie_token`, `name`, `role`, `hashed_password`, `email`, `is_email_activated`, `email_token`, `email_last_verification`, `profile_picture`) VALUES ('1', NULL, NULL, 'Anna Collier', 'admin', '$2y$10$oomRp.tNyq2sG/3YE3jtMO3lyCzBwI3dxWxEsz956a7Cherfp7h4K', 'admin@demo.com', '1', NULL, NULL, 'default.png'),
('2', NULL, NULL, 'Evans Fuller', 'user', '$2y$10$CUiHm3w/waT3xlY0mJBm/uYNbOQnHcXYaZifekyUGxJhCxDdjWsV6', 'user@demo.com', '1', NULL, NULL, 'default.png');
