<?php

/**
 * This file contains package_quiqqer_multi-mailer_ajax_backend_test
 */

QUI::$Ajax?->registerFunction(
    'package_quiqqer_multi-mailer_ajax_backend_test',
    function ($serverData) {
        $serverData = json_decode($serverData, true);

        try {
            $mail = QUI\MultiMailer\Mailer::parseMailServerDataToPhpMailer($serverData);

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
