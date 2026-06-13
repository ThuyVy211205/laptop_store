<?php
/**
 * Simple SMTP Mailer — Gmail SMTP, STARTTLS, hỗ trợ CID inline images
 */
class Mailer
{
    private $socket;
    private $smtpUser;
    private $smtpPass;

    private function __construct($user, $pass)
    {
        $this->smtpUser = $user;
        $this->smtpPass = $pass;
    }

    private function connect()
    {
        $this->socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 30);
        if (!$this->socket) {
            throw new RuntimeException("Không kết nối được SMTP: $errstr ($errno)");
        }
        stream_set_timeout($this->socket, 30);
        $this->read();
    }

    private function read()
    {
        $data = '';
        while (!feof($this->socket)) {
            $line = fgets($this->socket, 515);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }

    private function cmd($command)
    {
        fputs($this->socket, $command . "\r\n");
        return $this->read();
    }

    private function startsWith($str, $prefix)
    {
        return strncmp($str, $prefix, strlen($prefix)) === 0;
    }

    /**
     * @param array $inlineImages  [['cid'=>'img1@x','path'=>'/abs/path','mime'=>'image/webp'], ...]
     */
    private function doSend($toEmail, $toName, $subject, $htmlBody, array $inlineImages = [])
    {
        $this->connect();
        $this->cmd("EHLO localhost");

        $resp = $this->cmd("STARTTLS");
        if (!$this->startsWith($resp, '220')) {
            throw new RuntimeException("STARTTLS không được hỗ trợ: $resp");
        }

        $method = defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')
            ? (STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT)
            : STREAM_CRYPTO_METHOD_TLS_CLIENT;

        if (!stream_socket_enable_crypto($this->socket, true, $method)) {
            throw new RuntimeException("Không thể bật TLS");
        }

        $this->cmd("EHLO localhost");
        $this->cmd("AUTH LOGIN");
        $this->cmd(base64_encode($this->smtpUser));
        $resp = $this->cmd(base64_encode($this->smtpPass));
        if (!$this->startsWith($resp, '235')) {
            throw new RuntimeException("Xác thực Gmail thất bại: $resp");
        }

        $this->cmd("MAIL FROM:<" . $this->smtpUser . ">");
        $resp = $this->cmd("RCPT TO:<$toEmail>");
        if (!$this->startsWith($resp, '250')) {
            throw new RuntimeException("Địa chỉ nhận bị từ chối: $resp");
        }

        $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $encodedName    = "=?UTF-8?B?" . base64_encode($toName)   . "?=";

        // Lọc chỉ các ảnh có file thật
        $validImages = array_filter($inlineImages, fn($img) => !empty($img['path']) && file_exists($img['path']));

        if (empty($validImages)) {
            // Email HTML đơn giản (không có ảnh inline)
            $msg  = "From: " . MAIL_FROM_NAME . " <" . $this->smtpUser . ">\r\n";
            $msg .= "To: $encodedName <$toEmail>\r\n";
            $msg .= "Subject: $encodedSubject\r\n";
            $msg .= "MIME-Version: 1.0\r\n";
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n";
            $msg .= "X-Mailer: VQSTORE Mailer\r\n";
            $msg .= "\r\n";
            $msg .= chunk_split(base64_encode($htmlBody));
        } else {
            // multipart/related — ảnh nhúng CID
            $boundary = 'VQ_' . bin2hex(random_bytes(8));

            $msg  = "From: " . MAIL_FROM_NAME . " <" . $this->smtpUser . ">\r\n";
            $msg .= "To: $encodedName <$toEmail>\r\n";
            $msg .= "Subject: $encodedSubject\r\n";
            $msg .= "MIME-Version: 1.0\r\n";
            $msg .= "Content-Type: multipart/related; boundary=\"$boundary\"\r\n";
            $msg .= "X-Mailer: VQSTORE Mailer\r\n";
            $msg .= "\r\n";

            // Phần HTML
            $msg .= "--$boundary\r\n";
            $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
            $msg .= "Content-Transfer-Encoding: base64\r\n";
            $msg .= "\r\n";
            $msg .= chunk_split(base64_encode($htmlBody));
            $msg .= "\r\n";

            // Các ảnh inline
            foreach ($validImages as $img) {
                $imgData = base64_encode(file_get_contents($img['path']));
                $msg .= "--$boundary\r\n";
                $msg .= "Content-Type: " . $img['mime'] . "; name=\"" . basename($img['path']) . "\"\r\n";
                $msg .= "Content-Transfer-Encoding: base64\r\n";
                $msg .= "Content-ID: <" . $img['cid'] . ">\r\n";
                $msg .= "Content-Disposition: inline; filename=\"" . basename($img['path']) . "\"\r\n";
                $msg .= "\r\n";
                $msg .= chunk_split($imgData);
                $msg .= "\r\n";
            }

            $msg .= "--$boundary--\r\n";
        }

        $msg .= "\r\n.";

        $this->cmd("DATA");
        $resp = $this->cmd($msg);
        $this->cmd("QUIT");
        @fclose($this->socket);

        if (!$this->startsWith($resp, '250')) {
            throw new RuntimeException("Gửi email thất bại: $resp");
        }
        return true;
    }

    /**
     * @param array $inlineImages  Mảng ảnh CID inline (tuỳ chọn)
     */
    public static function send(
        $toEmail, $toName, $subject, $htmlBody,
        &$errorMsg    = '',
        $smtpUser     = null,
        $smtpPass     = null,
        array $inlineImages = []
    ) {
        $user = $smtpUser ?? SMTP_USER;
        $pass = $smtpPass ?? SMTP_PASS;

        if (!$pass) {
            $errorMsg = "App Password chưa được cấu hình cho tài khoản $user";
            error_log($errorMsg);
            return false;
        }
        try {
            $m = new self($user, $pass);
            return $m->doSend($toEmail, $toName, $subject, $htmlBody, $inlineImages);
        } catch (RuntimeException $e) {
            $errorMsg = $e->getMessage();
            error_log('Mailer error: ' . $errorMsg);
            return false;
        }
    }
}
