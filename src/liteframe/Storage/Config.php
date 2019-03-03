<?php

namespace LiteFrame\Storage;


class Config
{

    protected static $instance;
    protected $config;


    protected function __construct()
    {
    }

    /**
     * Return singleton class instance.
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Get the filename and key of the config name
     * @param $name
     * @return array
     */
    public function getInfo($name)
    {
        $keys = explode('.', $name);
        if (is_array($keys)) {
            $file = array_shift($keys);
            $key = implode('.', $keys);
        } else {
            $file = $name;
            $key = null;
        }

        return ['file' => $file, 'key' => $key];
    }

    public function getValue($name, $default = null)
    {
        $info = $this->getInfo($name);
        $file = $info['file'];
        $key = $info['key'];

        //Load config
        if (!isset($this->config[$file])) {
            $path = basePath("components/config/{$file}.php");
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                $this->config[$file] = (array)require $path;
            } else {
                $this->config[$file] = [];
            }
        }

        if(is_array($this->config[$file])) {
            return arrayDotSearch($this->config[$file], $key, $default);
        }else{
            return $this->config[$file];
        }
    }

    public function setValue($name, $value)
    {
        $info = $this->getInfo($name);
        $file = $info['file'];

        if (isset($this->config[$file]) && !empty($info['key'])) {

            $loc = &$this->config[$file];
            $keys = explode('.', $info['key']);
            foreach ($keys as $step) {
                $loc = &$loc[$step];
            }
            $loc = $value;
        } else {
            $this->config[$file] = $value;
        }
    }

    public static function get($name, $default = null)
    {
        return self::getInstance()->getValue($name, $default);
    }

    public static function set($name, $value)
    {
        self::getInstance()->setValue($name, $value);
    }

}