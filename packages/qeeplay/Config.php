<?php

namespace qeeplay;

/**
 * 用与保存和读取应用程序设置的工具类
 */
abstract class Config
{
    /**
     * 应用程序设置
     *
     * @var array
     */
    public static $_config = array();

    /**
     * 导入设置
     *
     * @param array $config
     */
    static function import(array $config)
    {
        self::$_config = $config + self::$_config;
    }

    /**
     * 读取指定的设置，如果不存在则返回 $default 参数指定的默认值
     *
     * 如果 $item 参数是一个数组，则依次查询指定的设置。
     *
     * @param string|array $item
     * @param mixed $default
     *
     * @return mixed
     */
    static function get($item, $default = null)
    {
        if (is_array($item))
        {
            foreach ($item as $key)
            {
                if (array_key_exists($key, self::$_config)) return self::$_config[$key];
            }
            return $default;
        }
        return array_key_exists($item, self::$_config) ? self::$_config[$item] : $default;
    }

    /**
     * 修改指定的设置
     *
     * @param string $item
     * @param mixed $value
     */
    static function set($item, $value)
    {
        self::$_config[$item] = $value;
    }

    /**
     * 删除指定的设置
     *
     * @param string $item
     */
    static function remove($item)
    {
        unset(self::$_config[$item]);
    }
}

Config::import(require(__DIR__ . '/__defaults.php'));
