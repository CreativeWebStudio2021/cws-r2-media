<?php

namespace CWS\R2Media\Admin;

defined('ABSPATH') || exit;

final class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
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

    public function registerSettings(): void
    {
        register_setting(
            'cws_r2_media_settings_group',
            'cws_r2_media_settings',
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitizeSettings'],
                'default'           => [],
            ]
        );

        add_settings_section(
            'cws_r2_media_cloudflare_section',
            'Configurazione Cloudflare R2',
            [$this, 'renderSectionDescription'],
            'cws-r2-media'
        );

        $fields = [
            'bucket' => [
                'label'       => 'Nome bucket',
                'placeholder' => 'trcgiornale-media',
            ],
            'endpoint' => [
                'label'       => 'Endpoint R2',
                'placeholder' => 'https://ACCOUNT_ID.r2.cloudflarestorage.com',
            ],
            'public_domain' => [
                'label'       => 'Dominio pubblico',
                'placeholder' => 'https://media.trcgiornale.it',
            ],
            'access_key' => [
                'label'       => 'Access Key ID',
                'placeholder' => '',
            ],
            'secret_key' => [
                'label'       => 'Secret Access Key',
                'placeholder' => 'Lascia vuoto per mantenere quello esistente',
                'type'        => 'password',
            ],
        ];

        foreach ($fields as $key => $field) {
            add_settings_field(
                'cws_r2_media_' . $key,
                $field['label'],
                [$this, 'renderTextField'],
                'cws-r2-media',
                'cws_r2_media_cloudflare_section',
                [
                    'key'         => $key,
                    'type'        => $field['type'] ?? 'text',
                    'placeholder' => $field['placeholder'],
                ]
            );
        }

        add_settings_field(
            'cws_r2_media_keep_local',
            'Mantieni copia locale',
            [$this, 'renderCheckboxField'],
            'cws-r2-media',
            'cws_r2_media_cloudflare_section',
            [
                'key'         => 'keep_local',
                'description' => 'Mantiene sul server originale e miniature dopo il caricamento su R2.',
            ]
        );

        add_settings_field(
            'cws_r2_media_rewrite_urls',
            'Servi immagini da R2',
            [$this, 'renderCheckboxField'],
            'cws-r2-media',
            'cws_r2_media_cloudflare_section',
            [
                'key'         => 'rewrite_urls',
                'description' => 'Riscrive gli URL delle immagini usando il dominio pubblico configurato.',
            ]
        );
    }

    public function renderSectionDescription(): void
    {
        echo '<p>';
        echo esc_html(
            'Inserisci le credenziali S3 generate nella sezione R2 di Cloudflare.'
        );
        echo '</p>';
    }

    public function renderTextField(array $args): void
    {
        $settings = $this->getSettings();
        $key = (string) $args['key'];
        $type = (string) ($args['type'] ?? 'text');
        $placeholder = (string) ($args['placeholder'] ?? '');

        /*
         * Non mostriamo mai il Secret Access Key già salvato.
         */
        $value = $key === 'secret_key'
            ? ''
            : (string) ($settings[$key] ?? '');

        printf(
            '<input
                type="%1$s"
                id="cws_r2_media_%2$s"
                name="cws_r2_media_settings[%2$s]"
                value="%3$s"
                placeholder="%4$s"
                class="regular-text"
                autocomplete="%5$s"
            >',
            esc_attr($type),
            esc_attr($key),
            esc_attr($value),
            esc_attr($placeholder),
            $key === 'secret_key' ? 'new-password' : 'off'
        );

        if ($key === 'secret_key' && !empty($settings['secret_key'])) {
            echo '<p class="description">';
            echo esc_html(
                'Una chiave è già configurata. Lascia il campo vuoto per non modificarla.'
            );
            echo '</p>';
        }
    }

    public function renderCheckboxField(array $args): void
    {
        $settings = $this->getSettings();
        $key = (string) $args['key'];
        $description = (string) ($args['description'] ?? '');

        printf(
            '<label for="cws_r2_media_%1$s">
                <input
                    type="checkbox"
                    id="cws_r2_media_%1$s"
                    name="cws_r2_media_settings[%1$s]"
                    value="1"
                    %2$s
                >
                %3$s
            </label>',
            esc_attr($key),
            checked(!empty($settings[$key]), true, false),
            esc_html($description)
        );
    }

    public function sanitizeSettings(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $existing = $this->getSettings();

        $settings = [
            'bucket' => sanitize_text_field(
                (string) ($input['bucket'] ?? '')
            ),
            'endpoint' => esc_url_raw(
                rtrim((string) ($input['endpoint'] ?? ''), '/')
            ),
            'public_domain' => esc_url_raw(
                rtrim((string) ($input['public_domain'] ?? ''), '/')
            ),
            'access_key' => sanitize_text_field(
                (string) ($input['access_key'] ?? '')
            ),
            'keep_local' => !empty($input['keep_local']) ? 1 : 0,
            'rewrite_urls' => !empty($input['rewrite_urls']) ? 1 : 0,
        ];

        $newSecret = trim((string) ($input['secret_key'] ?? ''));

        $settings['secret_key'] = $newSecret !== ''
            ? $newSecret
            : (string) ($existing['secret_key'] ?? '');

        if (
            $settings['endpoint'] !== ''
            && !str_ends_with(
                parse_url($settings['endpoint'], PHP_URL_HOST) ?: '',
                '.r2.cloudflarestorage.com'
            )
        ) {
            add_settings_error(
                'cws_r2_media_settings',
                'cws_r2_media_invalid_endpoint',
                'L’endpoint non sembra appartenere a Cloudflare R2.',
                'warning'
            );
        }

        if (
            $settings['public_domain'] !== ''
            && !str_starts_with($settings['public_domain'], 'https://')
        ) {
            add_settings_error(
                'cws_r2_media_settings',
                'cws_r2_media_invalid_domain',
                'Il dominio pubblico deve utilizzare HTTPS.',
                'error'
            );
        }

        return $settings;
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1>CWS R2 Media</h1>

            <?php settings_errors('cws_r2_media_settings'); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('cws_r2_media_settings_group');
                do_settings_sections('cws-r2-media');
                submit_button('Salva configurazione');
                ?>
            </form>
        </div>
        <?php
    }

    private function getSettings(): array
    {
        $settings = get_option('cws_r2_media_settings', []);

        return is_array($settings) ? $settings : [];
    }
}