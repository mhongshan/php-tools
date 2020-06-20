<?php

namespace mhs\tools\logger;

use Monolog\Logger as MonoLogger;

class Logger
{
    /**
     * @var string $channel
     */
    protected $channel;
    /**
     * @var Handler $handler
     */
    protected $handler;
    /**
     * @var HandlerConfig
     */
    protected $handlerConfig;
    /**
     * @var array $config
     */
    protected $config = [
        'channel' => 'default',
        'handler' => 'stream',
    ];
    /**
     * @var string $defaultHandler
     */
    protected $defaultHandler = 'stream';
    /**
     * @var array $channels
     */
    protected $channels = [];
    /**
     * @var MonoLogger $logger
     */
    protected $logger;

    public function __construct($config = [])
    {
        $this->setConfig($config);
        $this->setChannel($this->config['channel']);
        $this->handlerConfig = new HandlerConfig();
        $this->handler = new Handler();
    }

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     * @return self
     */
    public function setChannel(string $channel): Logger
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultHandler(): string
    {
        return $this->defaultHandler;
    }

    /**
     * @param string $defaultHandler
     * @return self
     */
    public function setDefaultHandler(string $defaultHandler): Logger
    {
        $this->defaultHandler = $defaultHandler;

        return $this;
    }

    /**
     * @return MonoLogger
     */
    public function getLogger(): MonoLogger
    {
        return $this->logger;
    }

    public function __call($name, $arguments)
    {
        if (!$this->logger) {
            $this->channel();
        }

        return call_user_func_array([$this->logger, $name], $arguments);
    }

    /**
     * @param string $channel
     * @param string $handler
     * @param array $config
     * @return MonoLogger
     */
    public function channel($channel = '', $handler = '', $config = [])
    {
        $this->channel = $channel ?: $this->config['channel'];
        $handler = $this->getHandlerName($handler);
        if (!isset($this->channels[$this->channel])) {
            $this->addChannel($this->channel, $handler, $this->getHandlerConfig($handler, $config));
        }

        return $this->logger = $this->channels[$this->channel][$handler];
    }

    public function getHandlerName($handler)
    {
        return ($handler && isset($this->config[$handler])) ? $handler : $this->defaultHandler;
    }

    /**
     * @param $channel
     * @param string $handler
     * @param array $config
     */
    public function addChannel($channel, $handler = '', $config = [])
    {
        $logger = new MonoLogger($channel);
        $config = $this->handlerConfig->setHandler($handler)->setName($channel)->setConfig($config)->parse();
        foreach ($config as $item) {
            $handlerObject = $this->handler->setHandler($handler)->setConfig($item)->createHandler();
            $logger->pushHandler($handlerObject);
        }

        $this->channels[$channel][$handler] = $logger;
    }

    /**
     * @param string $handler
     * @param array $config
     * @return array
     */
    public function getHandlerConfig($handler, $config)
    {
        $handler = $this->getHandlerName($handler);
        $defaultConfig = $this->config[$handler] ?? [];

        return array_merge($defaultConfig, $config);
    }
}