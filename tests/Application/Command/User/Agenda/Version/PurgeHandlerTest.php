<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Purge;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\PurgeHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class PurgeHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $dateTime = new \DateTime();

        // Mock
        $versionRepository = $this->prophesize(VersionRepositoryInterface::class);
        $generator = $this->prophesize(Generator::class);

        // Expected
        $generator->generate($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn(['version']);
        $versionRepository
            ->add(new User\Agenda\Version($event->reveal(), $user->reveal(), ['version'], $dateTime))
            ->shouldBeCalled();

        // Handler
        $purgeHandler = new PurgeHandler(
            $versionRepository->reveal(),
            $generator->reveal(),
            $dateTime
        );
        $purgeHandler->handle(new Purge($event->reveal(), $user->reveal()));
    }
}
