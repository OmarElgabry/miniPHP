<?php

 /**
  * This file contains configuration for the application
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */
 
 /**
  * Configuration for: Database Connection
  * Define database constants to establish a connection.
  *
  * It's important to set the charset for the database
  *
  * In the real world, you will have a database user with limited privileges(usually only CRUD).
  *
  */
 define("DB_HOST", "localhost");
 define("DB_NAME", "miniphp_68dozftn");
 define("DB_USER", "root");
 define("DB_PASS", "");
 define('DB_CHARSET', 'utf8');
 
 /**
  * Configuration for: Paths
  * Paths from views directory
  */

 define("VIEWS_PATH", APP . "views/");
 define("ERRORS_PATH", APP . "views/errors/");
 define("LOGIN_PATH", APP . "views/login/");
 define("ADMIN_VIEWS_PATH", VIEWS_PATH . "admin/");

/**
 * Configuration for: Cookies
 * 
 * COOKIE_RUNTIME: How long should a cookie be valid by seconds.
 *      - 1209600 means 2 weeks
 *      - 604800 means 1 week
 * COOKIE_DOMAIN: The domain where the cookie is valid for.
 *      COOKIE_DOMAIN mightn't work with "localhost", ".localhost", "127.0.0.1", or ".127.0.0.1". If so, leave it as empty string, false or null.
 *      @see http://stackoverflow.com/questions/1134290/cookies-on-localhost-with-explicit-domain
 *      @see http://php.net/manual/en/function.setcookie.php#73107
 *
 * COOKIE_PATH: The path where the cookie is valid for. If set to '/', the cookie will be available within the entire COOKIE_DOMAIN.
 * COOKIE_SECURE: If the cookie will be transferred through secured connection(SSL). It's highly recommended to set it to true if you have secured connection
 * COOKIE_HTTP: If set to true, Cookies that can't be accessed by JS - Highly recommended!
 * COOKIE_SECRET_KEY: A random value to make the cookie more secure.
 *
 */
 define("COOKIE_EXPIRY", 1209600);
 define("SESSION_COOKIE_EXPIRY", 604800);
 define("COOKIE_DOMAIN", '');
 define("COOKIE_PATH", '/');
 define("COOKIE_SECURE", false);
 define("COOKIE_HTTP", true);
 define("COOKIE_SECRET_KEY", "af&70-GF^!a{f64r5@g38l]#kQ4B+43%");


 /**
  * Configuration for: Encryption Keys
  *
  */
 define("ENCRYPTION_KEY", "3¥‹a0cd@!$251Êìcef08%&");
 define("HMAC_SALT", "a8C7n7^Ed0%8Qfd9K4m6d$86Dab");
 define("HASH_KEY", "z4D8Mp7Jm5cH");

/**
 * Configuration for: Email server credentials
 * Emails are sent using SMTP, Don't use built-in mail() function in PHP.
 *
 */
 define("EMAIL_SMTP_DEBUG", 2);
 define("EMAIL_SMTP_AUTH", true);
 define("EMAIL_SMTP_SECURE", "ssl");
 define("EMAIL_SMTP_HOST", "YOURSMTPHOST");
 define("EMAIL_SMTP_PORT", 465);
 define("EMAIL_SMTP_USERNAME", "YOURUSERNAME");
 define("EMAIL_SMTP_PASSWORD", "YOURPASSWORD");
 define("EMAIL_FROM", "info@YOURDOMAIN.com");
 define("EMAIL_FROM_NAME", "mini PHP");
 define("EMAIL_REPLY_TO", "no-reply@YOURDOMAIN.com");
 define("ADMIN_EMAIL", "YOUREMAIL");


/**
 * Configuration for: Email Verification
 *
 * EMAIL_EMAIL_VERIFICATION_URL: Full URL must be provided
 *
 */
 define("EMAIL_EMAIL_VERIFICATION", "1");
 define("EMAIL_EMAIL_VERIFICATION_URL", PUBLIC_ROOT . "Login/verifyUser");
 define("EMAIL_EMAIL_VERIFICATION_SUBJECT", "[IMP] Please verify your account");

 
/**
 * Configuration for: Reset Password
 *
 * EMAIL_PASSWORD_RESET_URL: Full URL must be provided
 *
 */
 define("EMAIL_PASSWORD_RESET", "2");
 define("EMAIL_PASSWORD_RESET_URL", PUBLIC_ROOT . "Login/resetPassword");
 define("EMAIL_PASSWORD_RESET_SUBJECT", "[IMP] Reset your password");

 /**
  * Configuration for: Report Bug, Feature, or Enhancement
  */
 define("EMAIL_REPORT_BUG", "3");
 define("EMAIL_REPORT_BUG_SUBJECT", "Request");


/**
 * Configuration for: Hashing strength
 *
 * It defines the strength of the password hashing/salting. "10" is the default value by PHP.
 * @see http://php.net/manual/en/function.password-hash.php
 *
 */
 define("HASH_COST_FACTOR", "10");

 /**
  * Configuration for: Pagination
  *
  */
 define("PAGINATION_DEFAULT_LIMIT", 10);

