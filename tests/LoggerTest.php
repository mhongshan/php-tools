<?php

namespace mhs\tools\tests;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLogger()
    {
        $config = [
            'channel' => 'test',
            'handler' => 'stream',
            'stream' => [
                'path' => __DIR__ . '/../logs',
            ]
        ];
        $logger = new \mhs\tools\logger\Logger($config);
        $this->assertIsObject($logger);
        $this->assertEquals($config['channel'], $logger->getChannel());
        $this->assertInstanceOf(Logger::class, $logger->channel());
        $logger->info('test', ['id' => 1]);
        $file = __DIR__ . '/../logs/test.log';
        $this->assertFileExists($file);
        $this->assertStringContainsString(json_encode(['id' => 1]), file_get_contents($file));
        @unlink($file);

        $logger->channel('test1')->info('test1', ['id' => 2]);
        $file = __DIR__ . '/../logs/test1.log';
        $this->assertFileExists($file);
        $this->assertStringContainsString(json_encode(['id' => 2]), file_get_contents($file));
        @unlink($file);
    }
}