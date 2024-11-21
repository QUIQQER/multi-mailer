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

        $needles = [
            'MAILFrom',
            'MAILFromText',
            'MAILReplyTo',
            'server',
            'port',
            'auth',
            'username',
            'password',
            'security',
            'debug',
            'secureSSL_verify_peer',
            'secureSSL_verify_peer_name',
            'secureSSL_allow_self_signed'
        ];

        foreach ($config as $section => $params) {
            if (!str_starts_with($section, 'server-')) {
                continue;
            }

            foreach ($needles as $needle) {
                if (!isset($params[$needle])) {
                    $params[$needle] = '';
                }
            }

            $servers[] = $params;
        }

        return $servers;
    },
    [],
    'Permission::checkAdminUser'
);
