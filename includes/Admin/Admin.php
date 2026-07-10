<?php

namespace CWS\R2Media\Admin;

defined('ABSPATH') || exit;

final class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'CWS R2 Media',
            'CWS R2 Media',
            'manage_options',
            'cws-r2-media',
            [$this, 'renderPage'],
            'dashicons-cloud',
            58
        );
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>CWS R2 Media</h1>';
        echo '<p>Plugin inizializzato correttamente.</p>';
        echo '</div>';
    }
}