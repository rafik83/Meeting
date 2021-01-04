<?php

namespace Proximum\Vimeet\Tests\Domain\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\User\Phone\PhoneSanitizer;

class PhoneSanitizerTest extends TestCase
{
    /**
     * @var PhoneSanitizer
     */
    private $phoneSanitizer;

    public function setUp()
    {
        $this->phoneSanitizer = new PhoneSanitizer();
    }

    public function phoneProvider()
    {
        return [
            ['+33122334455', '+33122334455'],
            ['78678678687686', '+78678678687686'],
            ['++33 768 76 78676', '+337687678676'],
            ['+33.768.(0)7678676A', '+3376807678676'],
        ];
    }

    /**
     * @dataProvider phoneProvider
     *
     * @param string $phone
     * @param string $expectedPhone
     */
    public function testHandle($phone, $expectedPhone)
    {
        $this->assertEquals($expectedPhone, $this->phoneSanitizer->handle($phone));
    }
}
