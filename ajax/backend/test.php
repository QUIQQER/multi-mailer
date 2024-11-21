<?php

/**
 * This file contains package_quiqqer_multi-mailer_ajax_backend_test
 */

use PHPMailer\PHPMailer\PHPMailer;

QUI::$Ajax->registerFunction(
    'package_quiqqer_multi-mailer_ajax_backend_test',
    function ($serverData) {
        $serverData = json_decode($serverData, true);

        try {
            $mail = QUI::getMailManager()->getPHPMailer();
            $mail->isSMTP();

            if (!empty($serverData['server'])) {
                $mail->Host = $serverData['server'];
            }

            if (!empty($serverData['port'])) {
                $mail->Port = $serverData['port'];
            }

            if (!empty($serverData['auth'])) {
                $mail->SMTPAuth = true;

                if (!empty($serverData['username'])) {
                    $mail->Username = $serverData['username'];
                }

                if (!empty($serverData['password'])) {
                    $mail->Username = $serverData['password'];
                }
            }

            if (!empty($serverData['security'])) {
                switch ($serverData['security']) {
                    case 'ssl':
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        break;

                    case 'tls':
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        break;
                }
            }

            // test mail
            if (!empty($serverData['MAILFrom'])) {
                if (!empty($serverData['MAILFromText'])) {
                    $mail->setFrom($serverData['MAILFrom'], $serverData['MAILFromText']);
                } else {
                    $mail->setFrom($serverData['MAILFrom'], '');
                }
            }

            if (!empty($serverData['MAILReplyTo'])) {
                $mail->addReplyTo($serverData['MAILReplyTo']);
            }

            $adminMails = QUI::conf('mail', 'admin_mail');
            $adminMails = explode(',', $adminMails);

            foreach ($adminMails as $adminMail) {
                $mail->addAddress($adminMail);
            }

            $mail->isHTML();
            $mail->Subject = QUI::getLocale()->get('quiqqer/multi-mailer', 'mail.test.subject');
            $mail->Body = QUI::getLocale()->get('quiqqer/multi-mailer', 'text.mail.body');
            $mail->AltBody = QUI::getLocale()->get('quiqqer/multi-mailer', 'mail.test.altBody');

            $mail->send();

            QUI::getMessagesHandler()->addSuccess(
                QUI::getLocale()->get('quiqqer/multi-mailer', 'message.mail.test.success')
            );
        } catch (Exception $e) {
            QUI::getMessagesHandler()->addError($e->getMessage());
        }
    },
    ['serverData'],
    'Permission::checkAdminUser'
);
