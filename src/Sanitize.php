<?php
namespace Avilio;
class Sanitize
{

    public static function clean($data, $type = 'html')
    {
        switch ($type) {
            case 'url':
                return esc_url($data);
            case 'attr':
                return esc_attr($data);
            case 'textarea':
                return wp_kses_post($data);
            case 'wswyig':
                return wp_kses_post($data);
            default:
                return esc_html($data);
        }
    }
}