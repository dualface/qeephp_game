<?php

namespace qeeplay\mvc;

use qeeplay\Config;

/**
 * App 类封装了一个基本的应用程序对象
 *
 * 如果需要定制应用程序对象，开发者可以从 App 派生自己的继承类。
 */
class App
{
    /**
     * URL 中用于指示请求动作的参数名称
     *
     * @var string
     */
    private $_action_accessor;

    /**
     * 应用程序基本路径
     *
     * @var string
     */
    private $_base_path;

    /**
     * 应用程序类所在的名字空间
     *
     * @var string
     */
    private $_app_namespace = 'app';

    /**
     * 工具对象集合
     *
     * @var array
     */
    private $_tools_instance = array();

    /**
     * 应用程序实例
     *
     * @var App
     */
    private static $_instance;

    /**
     * 构造函数
     *
     * @param string $namespace
     * @param string $base_path
     * @param bool $set_instance
     */
    function __construct($namespace, $base_path, $set_instance = true)
    {
        if ($set_instance) self::set_instance($this);
        $this->_action_accessor = Config::get(array('app.action_accessor', 'defaults.action_accessor'));
        $this->_app_namespace = $namespace;
        $this->_base_path = rtrim($base_path, '/\\');

        $timezone = Config::get(array('app.timezone', 'defaults.timezone'));
        date_default_timezone_set($timezone);

        $autoload_tools = Config::get(array('app.autoload_tools', 'defaults.autoload_tools'));
        $autoload_tools = arr($autoload_tools);
        foreach ($autoload_tools as $name)
        {
            $this->tool($name);
        }
    }

    /**
     * 设置应用程序实例
     *
     * @param App $app
     */
    static function set_instance(App $app)
    {
        self::$_instance = $app;
    }

    /**
     * 取得应用程序实例
     *
     * @return App
     */
    static function instance()
    {
        return self::$_instance;
    }

    /**
     * 返回应用程序根目录
     *
     * @return string
     */
    function base_path()
    {
        return $this->_base_path;
    }

    /**
     * 返回应用程序所在名字空间
     *
     * @return string
     */
    function app_namespace()
    {
        return $this->_app_namespace;
    }

    /**
     * 执行应用程序
     *
     * @param string $action_name
     *
     * @return mixed
     */
    function run($action_name = null)
    {
        static $session_not_started = true;
        if ($session_not_started && Config::get(array('app.session_autostart', 'defaults.session_autostart')))
        {
            session_start();
            $session_not_started = false;
        }

        // 解析请求 URL 中的动作名称
        if (is_null($action_name))
        {
            $action_name = request($this->_action_accessor);
            if (empty($action_name))
            {
                $action_name = Config::get(array('app.default_action', 'defaults.default_action'));
            }
        }
        $action_name = self::_format_action_name($action_name);

        // 动作对象
        $action_class_name = "{$this->_app_namespace}\\actions\\"
                             . str_replace('/', '\\', $action_name) . 'Action';
        $action_class_name = explode('\\', $action_class_name);
        array_push($action_class_name, ucfirst(array_pop($action_class_name)));
        $action_class_name = implode('\\', $action_class_name);

        if (!class_exists($action_class_name))
        {
            return $this->_process_result($this->_on_action_not_found($action_class_name));
        }

        // 执行动作
        $action = new $action_class_name($this, $action_name);
        /* @var $action BaseAction */
        $action->execute_wrapper();
        return $this->_process_result($action->result);
    }

    /**
     * 生成 URL
     *
     * @param string $action_name
     * @param array $params
     *
     * @return string
     */
    function url($action_name, array $params = null)
    {
        $action_name = self::_format_action_name($action_name);
        if (!$action_name)
        {
            $action_name = Config::get(array('app.default_action', 'defaults.default_action'));
        }
        $url = get_request_baseuri();
        $url .= "?{$this->_action_accessor}={$action_name}";
        if ($params)
        {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }

    /**
     * 取得指定的视图对象
     *
     * @param string $viewname
     * @param array $vars
     *
     * @return View
     */
    function view($viewname, array $vars)
    {
        return new View($this->_base_path . '/views', $viewname, $vars);
    }

    /**
     * 根据 tools 设定创建并返回指定的工具对象
     *
     * @param string $toolname
     *
     * @return object
     */
    function tool($toolname)
    {
        if (!isset($this->_tools_instance[$toolname]))
        {
            $config = Config::get(array("app.tools.{$toolname}", "defaults.tools.{$toolname}"));
            if (is_array($config) && !empty($config['class']))
            {
                $class = $config['class'];
            }
            else
            {
                if (is_string($config) && !empty($config))
                {
                    $class = $config;
                }
                else
                {
                    $class = $this->_app_namespace . '\\tools\\' . ucfirst($toolname) . 'Tool';
                }
                if (!is_array($config)) $config = array();
            }
            $this->_tools_instance[$toolname] = new $class($this, $config);
        }
        return $this->_tools_instance[$toolname];
    }

    /**
     * 确定指定的工具对象是否存在
     *
     * @param string $toolName
     *
     * @return bool
     */
    function has_tool($toolName)
    {
        return isset($this->_tools[$toolName]);
    }

    /**
     * 处理动作对象的执行结果
     *
     * @param mixed $result
     */
    protected function _process_result($result)
    {
        $charset = Config::get(array('app.output_charset', 'defaults.output_charset'), 'utf-8');
        if (is_object($result) && method_exists($result, 'execute'))
        {
            if (!headers_sent())
            {
                header('X-Powered-By-QeePlay: ' . \QPLAY_VER);
                header("Content-Type: text/html; charset={$charset}");
            }
            return $result->execute();
        }
        elseif (is_string($result))
        {
            if (!headers_sent())
            {
                header('X-Powered-By-QeePlay: ' . \QPLAY_VER);
                header("Content-Type: text/html; charset={$charset}");
            }
            return $result;
        }
        else
        {
            return $result;
        }
    }

    /**
     * 指定的控制器或动作没有找到
     *
     * @param string $action_name
     */
    protected function _on_action_not_found($action_name)
    {
        throw ActionError::action_not_found_error($action_name);
    }

    /**
     * 格式化动作名称
     *
     * @param string $action_name
     *
     * @return string
     */
    protected static function _format_action_name($action_name)
    {
        $action_name = strtolower($action_name);
        if (strpos($action_name, '.') !== false)
        {
            $action_name = preg_replace('/\/+/', '/', $action_name);
        }
        $action_name = trim($action_name, ". \t\r\n\0\x0B");
        return preg_replace('/[^a-z\/]/', '', $action_name);
    }
}
