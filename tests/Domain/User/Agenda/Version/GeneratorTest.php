<?php

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;
use Proximum\Vimeet\Domain\User\Agenda\Version\VersionNormalizer;

class GeneratorTest extends TestCase
{
    public function testGenerate()
    {
        // Context
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);
        $request3 = $this->prophesize(Request::class);

        // Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $versionNormalizer = $this->prophesize(VersionNormalizer::class);

        // Expected
        $expected = [
            1 => ['request' => 1],
            2 => ['request' => 2],
            3 => ['request' => 3],
        ];

        $requestRepository
            ->getRequestsPlacedByEventAndUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$request1->reveal(), $request2->reveal(), $request3->reveal()]);
        $versionNormalizer
            ->normalize([$request1->reveal(), $request2->reveal(), $request3->reveal()])
            ->shouldBeCalled()
            ->willReturn($expected);

        // Generator
        $generator = new Generator($requestRepository->reveal(), $versionNormalizer->reveal());
        $result = $generator->generate($event->reveal(), $user->reveal());

        $this->assertEquals($expected, $result);
    }
}
