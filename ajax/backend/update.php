<?php

/**
 * This file contains package_quiqqer_multi-mailer_ajax_backend_update
 */

QUI::$Ajax?->registerFunction(
    'package_quiqqer_multi-mailer_ajax_backend_update',
    function ($servers) {
        $Config = QUI::getPackage('quiqqer/multi-mailer')->getConfig();
        $config = $Config?->toArray() ?? [];
        $servers = json_decode($servers, true);

        foreach ($config as $section => $params) {
            if (str_starts_with($section, 'server-')) {
                $Config->del($section);
            }
        }

        $c = 0;
        foreach ($servers as $server) {
            $serverData = [
                'MAILFrom' => $server['MAILFrom'] ?? '',
                'MAILFromText' => $server['MAILFromText'] ?? '',
                'MAILReplyTo' => $server['MAILReplyTo'] ?? '',
                'server' => $server['server'],
                'port' => $server['port'] ?? 25,
                'auth' => $server['auth'] ?? 0,
                'username' => $server['username'] ?? '',
                'password' => $server['password'] ?? '',
                'security' => $server['security'] ?? 'ssl',
                'debug' => $server['debug'] ?? 0,
                'secureSSL_verify_peer' => $server['secureSSL_verify_peer'] ?? '',
                'secureSSL_verify_peer_name' => $server['secureSSL_verify_peer_name'] ?? '',
                'secureSSL_allow_self_signed' => $server['secureSSL_allow_self_signed'] ?? '',
                'isFallbackServer' => $server['isFallbackServer'] ?? '',
            ];

            $Config?->setSection('server-' . $c, $serverData);
            $c++;
        }

        $Config?->save();
    },
    ['servers'],
    'Permission::checkAdminUser'
);
