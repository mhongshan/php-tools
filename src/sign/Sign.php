<?php
declare(strict_types=1);

namespace mhs\tools\sign;


use Exception;
use mhs\tools\sign\driver\ISignDriver;

/**
 * Class Sign
 * @package mhs\tools\sign
 * @method make($data) \mhs\tools\sign\driver\ISignDriver::make($data)
 * @method verify($sign, $data) \mhs\tools\sign\driver\ISignDriver::verify($sign, $data)
 * @method format($data) \mhs\tools\sign\driver\ISignDriver::format($data)
 * @method setKey($key) \mhs\tools\sign\driver\ISignDriver::setKey($key)
 */
class Sign
{
    /**
     * @var string $driver 签名驱动
     */
    private $driver = 'md5';
    /**
     * @var ISignDriver $handler 签名工具类
     */
    private $handler;

    /**
     * @var array $config 配置信息
     */
    private $config = [];

    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置配置信息
     * @param array $config
     * @return $this
     */
    public function setConfig($config = [])
    {
        $defaultConfig = [
            'driver' => $this->driver,
            'key' => '',
            'except' => ['sign']
        ];

        $this->config = $config + $defaultConfig;
        !is_array($this->config['except']) && $this->config['except'] = explode(',', $this->config['except']);

        return $this;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getHandler(), $name], $arguments);
    }

    /**
     * @return ISignDriver
     */
    public function getHandler(): ISignDriver
    {
        if (!$this->handler) {
            if (!class_exists($this->driver)) {
                $class = 'mhs\tools\sign\driver\\' . ucfirst($this->driver) . 'Driver';
                if (!class_exists($class)) {
                    throw new Exception('class ' . $class . ' not found');
                }
            } else {
                $class = $this->driver;
            }

            $this->handler = new $class($this->config);
        }

        return $this->handler;
    }

    /**
     * @param ISignDriver $handler
     */
    public function setHandler(ISignDriver $handler): void
    {
        $this->handler = $handler;
    }
}