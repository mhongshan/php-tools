<?php

namespace mhs\tools\sign\driver;


abstract class ASignDriver implements ISignDriver
{
    protected $config = [
        'key' => '',
        'except' => ['sign'],
    ];

    protected $key = '';

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->setKey($this->config['key']);
    }

    /**
     * 设置key
     * @param string|mixed $key
     * @return $this|mixed
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * 验证签名
     * @param string $sign 待验证签名
     * @param array $data 签名数据
     * @return bool
     */
    public function verify($sign, $data)
    {
        $generate = $this->make($data);

        return $sign === $generate;
    }

    /**
     * 格式化签名数据
     * @param array $data 签名数据
     * @return array|mixed
     */
    public function format($data)
    {
        $data = $this->preEachData($data);
        $result = [];
        foreach ($data as $key => $datum) {
            if ($this->skipData($datum, $key)) {
                continue;
            }
            $result[$key] = $datum;
        }

        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function preEachData($data)
    {
        ksort($data);
        reset($data);

        return $data;
    }

    /**
     * @param $data
     * @param $key
     * @return bool
     */
    protected function skipData($data, $key)
    {
        return is_null($data) ||
            '' === $data ||
            false === $data ||
            in_array($key, $this->config['except']) ||
            is_object($data);
    }

    /**
     * @param $data
     * @return false|string
     */
    protected function getDataStr($data)
    {
        $str = '';
        foreach ($data as $key => $datum) {
            $str .= "{$key}={$datum}&";
        }
        $str = substr($str, 0, -1);

        return $str;
    }
}