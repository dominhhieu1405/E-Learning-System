<?php

namespace Config;

class Settings
{
    private static $file = __DIR__ . '/settings.json';

    public static function get($key = null, $default = null)
    {
        if (!file_exists(self::$file)) {
            self::save(self::defaults());
        }
        $data = json_decode(file_get_contents(self::$file), true) ?: [];
        if ($key === null)
            return $data;
        return $data[$key] ?? $default;
    }

    public static function save($data)
    {
        $current = file_exists(self::$file) ? json_decode(file_get_contents(self::$file), true) : [];
        $newData = array_merge($current, $data);
        file_put_contents(self::$file, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function defaults()
    {
        return [
            'site_name' => 'Hệ thống Ôn thi DGNL',
            'plus_price_1m' => 25000,
            'plus_price_3m' => 22000,
            'plus_price_6m' => 20000,
            'pro_price_1m' => 50000,
            'pro_price_3m' => 44000,
            'pro_price_6m' => 40000,
            'contact_email' => 'luce@luce.moe',
            'contact_phone' => '0123456789'
        ];
    }
}
