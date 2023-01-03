<?php

namespace App\Lib;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mail Class 
 * @access public
 * @author Andrés Felipe Delgado <andresfdel13@hotmail.com>
 * @version 8.0
 */
class Mail extends PHPMailer
{
    public function __construct()
    {
        $this->CharSet = 'UTF-8';
        try {
            //Server settings
            $this->isSMTP();
            $this->SMTPAuth = true;
            $this->SMTPDebug = 0;                      // Enable verbose debug output
            $this->Host       = config()->MAIL_HOST;                    // Set the SMTP server to send through
            $this->Username   = config()->MAIL_USERNAME;                     // SMTP username
            $this->Password   = config()->MAIL_PASSWORD;                               // SMTP password
            $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $this->Port       = config()->MAIL_PORT;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $this->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->ErrorInfo}";
        }
    }
    /**
     * SendMail
     * (ES) Este método se encarga de realizar el envio de correos electronicos
     * @param array  $data
     * @param array  $data['Target_group']
     * Mail Recipient Group
     * @param array  $data['Blind_copy']
     * Hidden copy or carbon copy
     * @param array  $data['Attachment']
     * Attached
     * @param bool   $data['IsHTML']
     * Mail Format Definition
     * @param string $data['Subject']
     * message's subject
     * @param string $data['Body']
     * Body of the message in format html
     * @param string $data['AltBody']
     * Alternative message body for plain text
     * @param bool   $sender
     * Type of sending, native or instance user
     * @param array  $data_sender
     * Sender SMTP configuration
     * @param string $data_sender['host']
     * @param string $data_sender['username']
     * @param string $data_sender['password']
     * @param string $data_sender['port']
     * 
     * @return bool
     */
    public function SendMail(array $data = [], bool $sender = false, array $data_sender = []): bool
    {
        try {
            //validate sender type
            if ($sender != false) {
                //Server settings
                $this->SMTPDebug = (isset($data_sender['debug'])) ? intval($data_sender['debug']) : 0;                     // Enable verbose debug output
                $this->isSMTP();
                // Send using SMTP
                $this->Host       = $data_sender['host'];                    // Set the SMTP server to send through
                $this->SMTPAuth   = true;                                   // Enable SMTP authentication
                $this->Username   = $data_sender['username'];                     // SMTP username
                $this->Password   = $data_sender['password'];                               // SMTP password
                //$this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $this->Port       = $data_sender['port'];

                $this->setFrom($data_sender['username'], $data_sender['from'] ?? config()->MAIL_FROM_NAME);
                $this->addReplyTo($data_sender['username'], $data_sender['from'] ?? config()->MAIL_FROM_NAME);
            } else {
                $this->setFrom(config()->MAIL_SYSTEM, config()->MAIL_FROM_NAME);
                $this->addReplyTo(config()->EMAIL, config()->MAIL_FROM_NAME);
            }
            //Recipients
            if (isset($data['Target_group']) && !empty($data['Target_group'])) {
                foreach ($data['Target_group'] as $key) {
                    //Add Recipients
                    $this->addAddress($key);
                }
            }
            //  print_debug($this);


            //Recipients
            if (isset($data['Blind_copy']) && !empty($data['Blind_copy'])) {
                foreach ($data['Blind_copy'] as $key) {
                    //Add Recipients BCC
                    $this->addBCC($key);
                }
            }

            //Attachments
            if (isset($data['Attachment']) && !empty($data['Attachment'])) {
                foreach ($data['Attachment'] as $key) {
                    //Add attachments
                    $this->addAttachment($key);
                }
            }

            // Content mail
            if (isset($data['IsHTML']) && is_bool($data['IsHTML'])) {
                $data['IsHTML'] = ($data['IsHTML'] == true) ? true : false;
                $this->isHTML($data['IsHTML']); // Set email format to HTML
            } else {
                $this->isHTML(true); // Set email format to HTML
            }
            $this->Subject = (isset($data['Subject']) && !empty($data['Subject'])) ? $data['Subject'] : config()->APP_NAME . ' Message';
            $this->Body    = (isset($data['Body']) && !empty($data['Body'])) ? $data['Body'] : config()->APP_NAME . ' Message';
            $this->AltBody = (isset($data['AltBody']) && !empty($data['AltBody'])) ? $data['AltBody'] : config()->APP_NAME . ' Message';
            //print_debug($this);
            if ($this->send()) {
                return true;
            } else {
                return false;
            }
            //print_debug($this->ErrorInfo);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * SchemeDataSend()
     * (ES) Este método permite obtener mediante un arreglo la estructura
     * de datos requerida para el envio de un correo.
     */
    public function SchemeDataSend(): array
    {

        return  array(
            'Target_group' => (array) array(),
            'Blind_copy'   => (array) array(),
            'Attachment'   => (array) array(),
            'IsHTML'       => true,
            'Subject'      => (string) "",
            'Body'         => (string) "",
            'AltBody'      => (string) "",
        );
    }
}