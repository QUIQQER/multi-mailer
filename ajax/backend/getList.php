<?php

/**
 * This file contains package_quiqqer_multi-mailer_ajax_backend_getList
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_multi-mailer_ajax_backend_getList',
    function () {
        $Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();
        $config = $Config->toArray();
        $servers = [];

        foreach ($config as $section => $params) {
            if (str_starts_with($section, 'server-')) {
                $servers[] = $params;
            }
        }

        return $servers;
    },
    [],
    'Permission::checkAdminUser'
);
