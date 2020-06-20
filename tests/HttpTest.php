<?php

namespace mhs\tools\tests;

use mhs\tools\http\Client;
use mhs\tools\http\Response;
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    public function testSend()
    {
        $client = new Client();
        $baseUri = 'https://github.com/';
        $url = 'guzzle/guzzle';
        $client->setMethod('GET')->setData(['id' => 1])->setUri($url)->setBaseUri($baseUri);
        $this->assertEquals($baseUri . $url, $client->getUrl());
        $response = $client->send();
        $this->assertIsObject($response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertStringContainsString('guzzle', $response->getBody()->getContents());
    }
}