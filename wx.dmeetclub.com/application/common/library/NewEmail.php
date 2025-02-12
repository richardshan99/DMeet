<?php

namespace app\common\library;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use think\Log;

/**
 * 新的邮件类类
 */
class NewEmail
{
    /**
     * @param $address
     * @param $subject
     * @param $body
     * @return bool
     */
      public function send($address,$subject,$body): bool
      {
          $mail = new PHPMailer(true);

          try {
              $mail->SMTPDebug = SMTP::DEBUG_OFF;
              $mail->CharSet = 'UTF-8';
              $mail->isSMTP();
              $mail->Host = 'smtp.exmail.qq.com';
              $mail->SMTPAuth = true;
              $mail->Username = 'sv@dmeetclub.com';
              $mail->Password = 'V6RxD6SYE4bt3L3F';
              $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
              $mail->Port = 465;

              $mail->setFrom('sv@dmeetclub.com', 'DMeet直面');
              $mail->addAddress($address);

              $mail->isHTML(true);
              $mail->ContentType = 'text/html; charset=utf-8';
              $mail->Subject = $subject;

              $mail->Body = $body;

              $mail->send();
          } catch (\Throwable $e) {
              Log::init(['type'  =>  'File', 'path'  =>  ROOT_PATH.'logs/send_email/']);
              Log::write("邮件发送失败，Error: {$mail->ErrorInfo}");
              return false;
          }

         return true;
      }
}
