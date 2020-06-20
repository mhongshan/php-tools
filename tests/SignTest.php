<?php

namespace mhs\tools\tests;

use mhs\tools\sign\Sign;
use PHPUnit\Framework\TestCase;

class SignTest extends TestCase
{
    private $sign;
    private $data = [
        'id' => 1,
        'name' => 'zhangsan',
        'hasChildren' => true,
        'children' => [
            [
                'current' => [
                    'id' => '2'
                ],
                'children' => [],
            ]
        ],
        'desc' => '',
    ];

    public function testMd5()
    {
        $sign = $this->getSign();
        $sign->setDriver('md5');
        $sign->setKey('phpunit');
        $this->data['children'] = json_encode($this->data['children']);
        $data = $sign->format($this->data);
        $this->assertArrayNotHasKey('desc', $data);
        $signStr = $sign->make($this->data);
        $verify = $sign->verify($signStr, $this->data);
        $this->assertTrue($verify);
        $verifySign = '38bb524768af3515a867b8d88c4da712';
        $this->assertEquals($verifySign, $signStr);
    }

    protected function getSign()
    {
        if (!$this->sign) {
            $config = [
                'driver' => 'md5',
                'key' => '',
                'except' => 'sign'
            ];
            $this->sign = new Sign($config);
        }

        return $this->sign;
    }
}