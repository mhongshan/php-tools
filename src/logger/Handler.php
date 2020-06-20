<?php

namespace mhs\tools\logger;

use Cascader\Cascader;

class Handler
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var string $handler
     */
    protected $handler;

    /**
     * @param string $handler
     * @return self
     */
    public function setHandler(string $handler): Handler
    {
        $this->handler = ucwords(str_replace(['-', '_'], ' ', $handler));

        return $this;
    }

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): Handler
    {
        $this->config = $config;

        return $this;
    }


    public function createHandler()
    {
        $class = 'Monolog\Handler\\' . $this->handler . 'Handler';
        $vars = $this->parseConfig($this->config);
        $invoke = new Cascader();

        return $invoke->create($class, $vars);
    }

    /**
     * @param array $config
     * @return array
     */
    protected function parseConfig($config)
    {
        $method = 'parseConfig' . $this->handler;
        if (isset($config['_callback'])) {
            $config = call_user_func_array($config['_callback'], [$this, $config]);
        } elseif (method_exists($this, $method)) {
            $config = $this->{$method}($config);
        }

        return $config;
    }
}