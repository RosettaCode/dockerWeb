<?php declare(strict_types=1);

use PHPUNit\Framework\TestCase;
use Adoy\FastCGI\Client;

final class PHPFPMTest extends TestCase
{
    protected $instance;
    protected $client;

    public function setUp() : void {
        $this->instance = new PHPFPM();
        $this->client = new Client('127.0.0.1', PHPFPM::FCGI_PORT);
    }

    public function tearDown() : void {
        if($this->instance->status()) {
            $this->instance->stop();
        }
    }

    public function testPHPFPMRunning() {
        $this->assertTrue($this->instance->status());
    }

     public function testStop() {
         $this->instance->stop();
         $this->assertFalse($this->instance->status());
     }

     public function testFPMStatus() {
//         exec("apk add iproute2 >&2");
        $pid = getmypid();
         exec("ss -lp");
         $result = $this->client->request([
             'SCRIPT_NAME' => '/status',
             'SCRIPT_FILENAME' => '/status'
         ],
         NULL);

         $this->assertTrue(is_string($result['response']));
     }
}
