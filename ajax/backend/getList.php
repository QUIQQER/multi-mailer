<?php

/**
 * This file contains package_quiqqer_multi-mailer_ajax_backend_getList
 */

use QUI\MultiMailer\Mailer;

QUI::$Ajax->registerFunction(
    'package_quiqqer_multi-mailer_ajax_backend_getList',
    function () {
        return Mailer::getList();
    },
    [],
    'Permission::checkAdminUser'
);
