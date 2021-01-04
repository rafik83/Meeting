<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use Ovh\Api;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Infrastructure\Adapter\SMSBlackListAdapter;

class SMSBlackListAdapterTest extends TestCase
{
    public function testGetBlackList()
    {
        $api = $this->prophesize(Api::class);
        $api->get('/sms/ovh-service-name/blacklists')->shouldBeCalled()->willReturn(['+33123123123', '+3456565656']);

        $SMSBlackListAdapter = new SMSBlackListAdapter($api->reveal(), 'ovh-service-name');
        $result = $SMSBlackListAdapter->getBlackList();

        $expected = ['+33123123123', '+3456565656'];

        $this->assertEquals($expected, $result);
    }
}
