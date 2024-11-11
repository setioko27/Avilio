<?php
namespace Avilio;

use Avilio\Sanitize;

class ACF {
    private const DEFAULT_TARGET = '_self';
    private const ALLOWED_TYPES = [
        'text' => 'clean',
        'textarea' => 'textarea',
        'wswyig' => 'textarea',
        'link' => 'link',
        'repeater' => 'repeater',
        'image' => 'image'
    ];

    public static function field_object(string $field_name, $parent, bool $is_sub): ?array 
    {
        $method = $is_sub ? 'get_sub_field' : 'get_field';
        $object_method = $is_sub ? 'get_sub_field_object' : 'get_field_object';

        return [
            'object' => $object_method($field_name, $parent),
            'field' => $method($field_name, $parent)
        ];
    }

    public static function field(string $field_name, array $args = [], $parent = false, bool $is_sub = false, bool $is_sanitize = true)
    {
        $obj = self::field_object($field_name, $parent, $is_sub);
        
        if (!$is_sanitize) {
            return $obj['field'];
        }

        $object = $obj['object'] ?? null;
        if (!$object) {
            return null;
        }

        $type = $object['type'];
        $value = $obj['field'];

        return self::process_field_type($type, $value, $args, $field_name, $parent);
    }

    private static function process_field_type(string $type, $value, array $args, string $field_name, $parent)
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            return $value;
        }

        switch ($type) {
            case 'text':
                return Sanitize::clean($value);
            
            case 'textarea':
            case 'wswyig':
                return Sanitize::clean($value, 'textarea');
            
            case 'link':
                return self::process_link($value);
            
            case 'repeater':
                return self::repeater_data($field_name, $args, $parent);
            
            case 'image':
                return self::process_image($value, $args);
        }

        return $value;
    }

    private static function process_link($value): ?array
    {
        if (!$value || !isset($value['url'])) {
            return null;
        }

        return [
            'url' => Sanitize::clean($value['url'], 'url'),
            'target' => Sanitize::clean($value['target'] ?? self::DEFAULT_TARGET, 'attr'),
            'title' => Sanitize::clean($value['title'])
        ];
    }

    private static function process_image($value, array $args)
    {
        if (isset($args['required']) && $args['required']) {
            return get_image($value, $args['size'] ?? null, $args['default'] ?? 'default_image');
        }

        if (is_numeric($value)) {
            $size = $args['size'] ?? 'full';
            $img = wp_get_attachment_image_src($value, $size);
            return $img[0] ?? null;
        }

        return $value;
    }

    public static function option(string $name, array $args = [])
    {
        return self::field($name, $args, 'option');
    }

    public static function repeater_data(string $repeater_field, array $sub_fields = [], $parent = false): array
    {
        $data = [];
        
        if (!have_rows($repeater_field, $parent)) {
            return $data;
        }

        while (have_rows($repeater_field, $parent)) {
            the_row();
            $data[] = empty($sub_fields) 
                ? get_row(true) 
                : self::process_sub_fields($sub_fields);
        }

        return $data;
    }

    private static function process_sub_fields(array $sub_fields): array
    {
        $item = [];
        foreach ($sub_fields as $key => $field_name) {
            $field_key = is_numeric($key) ? $field_name : $key;
            $item[$field_key] = get_sub_field($field_name) ?? null;
        }
        return $item;
    }
}
