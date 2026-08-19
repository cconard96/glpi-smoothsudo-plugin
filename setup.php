<?php

/**
 * SPDX-FileCopyrightText: 2026 Curtis Conard
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use Glpi\Plugin\Hooks;
use Glpi\Security\ReAuth\ReAuthManager;

function plugin_init_smoothsudo()
{
    global $PLUGIN_HOOKS;

    $skip_active_reauth_extension = false;

    $PLUGIN_HOOKS[Hooks::INIT_SESSION]['smoothsudo'] = static function () use (&$skip_active_reauth_extension) {
        global $DB;

        $login_uid = Session::getLoginSessionUID();
        if ($login_uid) {
            $it = $DB->request([
                'SELECT' => ['logged_in_at'],
                'FROM' => 'glpi_users_sessionhistories',
                'WHERE' => [
                    'login_session_uid' => $login_uid,
                    'logged_out_at' => null,
                ],
                'LIMIT' => '1'
            ]);
            if ($it->count() === 0) {
                // Init session hook fires before the session is recorded in the DB
                // Add a 1 minute grace period to avoid immediately asking user for password/totp after login.
                $_SESSION['glpi_reauth_until'] = (new DateTime(Session::getCurrentTime()))->modify('+1 minute')->getTimestamp();
                $skip_active_reauth_extension = true;
            }
        }
    };

    $reauth_until = $_SESSION['glpi_reauth_until'] ?? null;

    if (!$skip_active_reauth_extension && !Toolbox::isAjax() && ReAuthManager::getInstance()->isReAuthenticated()) {
        // reset the glpi_reauth_until to be the current time + ReAuthManager::REAUTH_DELAY_SECONDS
        $_SESSION['glpi_reauth_until'] = (new DateTime(Session::getCurrentTime()))->modify('+'.ReAuthManager::REAUTH_DELAY_SECONDS.' seconds')->getTimestamp();
    }
}

function plugin_version_smoothsudo()
{
    return [
        'name'         => __('Smooth Re-authentication', 'smoothsudo'),
        'version'      => '1.0.0',
        'author'       => 'Curtis Conard',
        'license'      => 'GPLv3+',
        'homepage'     =>'https://github.com/cconard96/glpi-smoothsudo-plugin',
        'requirements' => [
            'glpi'   => [
                'min' => '12.0.0',
                'max' => '13.0.0'
            ]
        ]
    ];
}
