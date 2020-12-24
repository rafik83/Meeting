<?php

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\User\Agenda\Version\RequestVersionNormalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\VersionNormalizer;

class VersionNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);
        $request3 = $this->prophesize(Request::class);
        $request1->getId()->willReturn(1);
        $request2->getId()->willReturn(2);
        $request3->getId()->willReturn(3);
        $requests = [
            $request1->reveal(),
            $request2->reveal(),
            $request3->reveal(),
        ];

        // Mock
        $requestVersionNormalizer = $this->prophesize(RequestVersionNormalizer::class);
        $requestVersionNormalizer->normalize($request1->reveal())->shouldBeCalled()->willReturn(['request' => 1]);
        $requestVersionNormalizer->normalize($request2->reveal())->shouldBeCalled()->willReturn(['request' => 2]);
        $requestVersionNormalizer->normalize($request3->reveal())->shouldBeCalled()->willReturn(['request' => 3]);

        // VersionNormalizer
        $versionNormalizer = new VersionNormalizer($requestVersionNormalizer->reveal());
        $result = $versionNormalizer->normalize($requests);

        $expected = [
            1 => ['request' => 1],
            2 => ['request' => 2],
            3 => ['request' => 3],
        ];

        $this->assertEquals($expected, $result);
    }
}
