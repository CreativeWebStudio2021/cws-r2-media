<?php

namespace CWS\R2Media\Core;

use CWS\R2Media\Admin\Admin;

defined('ABSPATH') || exit;

final class Plugin
{
    public static function boot(): void
    {
        add_action(
            'plugins_loaded',
            static function (): void {
                new Admin();
            }
        );
    }
}