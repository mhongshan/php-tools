<?php

namespace mhs\tools\logger;

use Exception;
use Monolog\Logger as MonoLogger;

class HandlerConfig
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var string $handler
     */
    protected $handler = 'stream';
    /**
     * @var string $name
     */
    protected $name;
    /**
     * @var array
     */
    protected $defaultLogLevel = [
        MonoLogger::INFO,
        MonoLogger::WARNING,
        MonoLogger::ERROR,
    ];

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): HandlerConfig
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param string $handler
     * @return self
     */
    public function setHandler(string $handler): HandlerConfig
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): HandlerConfig
    {
        $this->name = $name ?: date('Ymd');

        return $this;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $config = [];
        foreach ($this->parseLevel() as $level) {
            $tmp = $this->config;
            $tmp['level'] = $level;
            $config[] = $this->parseHandlerConfig($tmp);
        }

        return $config;
    }

    /**
     * @return array
     */
    protected function parseLevel()
    {
        $level = isset($this->config['level']) && $this->config['level'] ?: $this->defaultLogLevel;
        !is_array($level) && $level = [$level];

        return $level;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function parseHandlerConfig($config)
    {
        if (!empty($config['_callback']) && is_callable($config['_callback'])) {
            return call_user_func_array($config['_callback'], [$this->handler, $config]);
        }
        $method = 'parse' . ucwords(str_replace(['-', '_'], ' ', $this->handler));
        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        return $config;
    }

    /**
     * @param array $config
     * @return mixed
     * @throws Exception
     */
    protected function parseStream($config)
    {
        if (!empty($config['stream'])) {
            return $config;
        }
        if (empty($config['path'])) {
            throw new Exception('path 参数必填');
        }
        $file = $config['path'] . '/' . $this->name . '.log';
        $config['stream'] = $file;

        return $config;
    }
}