<?php

class Mtaa_Admin_Settings_Sanitization
{
    public function __construct() {
        add_filter('mtaa_field_save_title_separator', 'esc_html');
    }
}