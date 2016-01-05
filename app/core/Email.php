<?php

 /**
  * Email Class
  *
  * Sending emails via SMTP.
  * It uses PHPMailer library to send emails.
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

 class Email{

     /**
      * This is the constructor for Email object.
      *
      * @access private
      */
     private function __construct(){}

     /**
      * send an email
      *
      * @access public
      * @static static method
      * @param  string  $type Email constant - check config.php
      * @param  string  $email
      * @param  array   $userData
      * @param  array   $data any associated data with the email
      * @throws Exception If failed to send the email
      */
     public static function sendEmail($type, $email, $userData, $data){

         $mail             = new PHPMailer();
         $mail->IsSMTP();

         // good for debugging, otherwise keep it commented
         // $mail->SMTPDebug  = EMAIL_SMTP_DEBUG;
         $mail->SMTPAuth   = Config::get('EMAIL_SMTP_AUTH');
         $mail->SMTPSecure = Config::get('EMAIL_SMTP_SECURE');
         $mail->Host       = Config::get('EMAIL_SMTP_HOST');
         $mail->Port       = Config::get('EMAIL_SMTP_PORT');
         $mail->Username   = Config::get('EMAIL_SMTP_USERNAME');
         $mail->Password   = Config::get('EMAIL_SMTP_PASSWORD');

         $mail->SetFrom(Config::get('EMAIL_FROM'), Config::get('EMAIL_FROM_NAME'));
         $mail->AddReplyTo(Config::get('EMAIL_REPLY_TO'));

         switch($type){
             case (Config::get('EMAIL_EMAIL_VERIFICATION')):
                 $mail->Body = self::getEmailVerificationBody($userData, $data);
                 $mail->Subject    = Config::get('EMAIL_EMAIL_VERIFICATION_SUBJECT');
                 $mail->AddAddress($email);
                 break;
             case (Config::get('EMAIL_REVOKE_EMAIL')):
                 $mail->Body = self::getRevokeEmailBody($userData, $data);
                 $mail->Subject    = Config::get('EMAIL_REVOKE_EMAIL_SUBJECT');
                 $mail->AddAddress($email);
                 break;
             case (Config::get('EMAIL_UPDATE_EMAIL')):
                 $mail->Body = self::getUpdateEmailBody($userData, $data);
                 $mail->Subject    = Config::get('EMAIL_UPDATE_EMAIL_SUBJECT');
                 $mail->AddAddress($email);
                 break;
             case (Config::get('EMAIL_PASSWORD_RESET')):
                 $mail->Body = self::getPasswordResetBody($userData, $data);
                 $mail->Subject    = Config::get('EMAIL_PASSWORD_RESET_SUBJECT');
                 $mail->AddAddress($email);
                 break;
             case (Config::get('EMAIL_REPORT_BUG')):
                 $mail->Body = self::getReportBugBody($userData, $data);
                 $mail->Subject    = "[".ucfirst($data["label"])."] " . Config::get('EMAIL_REPORT_BUG_SUBJECT') . " | " . $data["subject"];
                 $mail->AddAddress($email);
                 break;
         }

         // If you don't have an email setup, you can instead save emails in log.txt file using Logger.
         // Logger::log("EMAIL", $mail->Body);
         if(!$mail->Send()) {
             throw new Exception("Email couldn't be sent to ". $userData["id"] ." for type: ". $type);
         }
     }

     /**
      * Construct the body of Password Reset email
      *
      * @access private
      * @static static method
      * @param  array   $userData
      * @param  array   $data
      * @return string  The body of the email.
      */
     private static function getPasswordResetBody($userData, $data){

         $body = "";
         $body .= "Dear " . $userData["name"] . ", \n\nYou can reset your password from the following link: ";
         $body .= Config::get('EMAIL_PASSWORD_RESET_URL') . "?id=" . urlencode(Encryption::encryptId($userData["id"])) . "&token=" . urlencode($data["password_token"]);
         $body .= "\n\nIf you didn't request to reset your password, Please contact the admin directly.";
         $body .= "\n\nRegards\nmini PHP Team";

         return $body;
     }

     /**
      * Construct the body of Email Verification email
      *
      * @access private
      * @static static method
      * @param  array   $userData
      * @param  array   $data
      * @return string  The body of the email.
      *
      */
     private static function getEmailVerificationBody($userData, $data){

         $body  = "";
         $body .= "Dear " . $userData["name"] . ", \n\nPlease verify your email from the following link: ";
         $body .= Config::get('EMAIL_EMAIL_VERIFICATION_URL') . "?id=" . urlencode(Encryption::encryptId($userData["id"])) . "&token=" . urlencode($data["email_token"]);
         $body .= "\n\nIf you didn't edit/add your email, Please contact the admin directly.";
         $body .= "\n\nRegards\nmini PHP Team";

         return $body;
     }

     /**
      * Construct the body of Revoke Email Changes email
      *
      * @access private
      * @static static method
      * @param  array   $userData
      * @param  array   $data
      * @return string  The body of the email.
      *
      */
     private static function getRevokeEmailBody($userData, $data){

         $body  = "";
         $body .= "Dear " . $userData["name"] . ", \n\nYour email has been changed, You can revoke your changes from the following link: ";
         $body .= Config::get('EMAIL_REVOKE_EMAIL_URL') . "?id=" . urlencode(Encryption::encryptId($userData["id"])) . "&token=" . urlencode($data["email_token"]);
         $body .= "\n\nIf you didn't update your email, Please contact the admin directly.";
         $body .= "\n\nRegards\nmini PHP Team";

         return $body;
     }

     /**
      * Construct the body of Update Email email
      *
      * @access private
      * @static static method
      * @param  array   $userData
      * @param  array   $data
      * @return string  The body of the email.
      *
      */
     private static function getUpdateEmailBody($userData, $data){

         $body  = "";
         $body .= "Dear " . $userData["name"] . ", \n\nPlease confirm your new email from the following link: ";
         $body .= Config::get('EMAIL_UPDATE_EMAIL_URL') . "?id=" . urlencode(Encryption::encryptId($userData["id"])) . "&token=" . urlencode($data["pending_email_token"]);
         $body .= "\n\nIf you have no idea what is this email for, you can ignore it.";
         $body .= "\n\nRegards\nmini PHP Team";

         return $body;
     }

     /**
      * Construct the body of Report Bug, Feature or Enhancement email
      *
      * @access private
      * @static static method
      * @param  array   $userData
      * @param  array   $data
      * @return string  The body of the email.
      *
      */
     private static function getReportBugBody($userData, $data){

         $body  = "";
         $body .= "User: " . $userData["name"] . ", \n\n" . $data["message"];
         $body .= "\n\n\nFrom: " . $userData["id"] . " | " . $userData["name"];
         $body .= "\n\nRegards\nmini PHP Team";

         return $body;
     }

 }
	
