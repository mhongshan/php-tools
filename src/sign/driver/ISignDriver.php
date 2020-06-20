<?php

namespace mhs\tools\sign\driver;

interface ISignDriver
{
    /**
     * 生成签名
     * @param array $data 签名数据
     * @return string
     */
    public function make($data);

    /**
     * 验证签名
     * @param string $sign 待验证签名
     * @param array $data 签名数据
     * @return boolean
     */
    public function verify($sign, $data);

    /**
     * 签名数据格式化处理
     * @param array $data 签名数据
     * @return mixed
     */
    public function format($data);

    /**
     * 设置密钥
     * @param string|mixed $key 签名密钥
     * @return mixed
     */
    public function setKey($key);
}