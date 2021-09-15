<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\Import\CreateMapping;
use Proximum\Vimeet\Application\Command\Participant\Import\CreateMappingHandler;
use Proximum\Vimeet\Domain\Exception\Sheet\ImportMapping\TitleNotUniqueException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;

class CreateMappingHandlerTest extends TestCase
{
    public function testHandleNotUniqueTitle(): void
    {
        $this->expectException(TitleNotUniqueException::class);

        $date = new \DateTime();
        $event = $this->prophesize(Event::class);
        $repository = $this->prophesize(ImportMappingRepositoryInterface::class);
        $repository->add(Argument::any())->shouldNotBeCalled();
        $repository->hasExistingMappingWithTitle($event->reveal(), 'my unique title')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $command = new CreateMapping($event->reveal(), ['title' => 'abcdef', 'name' => 'azerty'], 'my unique title');
        $handler = new CreateMappingHandler(
            $repository->reveal(),
            $date
        );

        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $date = new \DateTime();
        $event = $this->prophesize(Event::class);
        $repository = $this->prophesize(ImportMappingRepositoryInterface::class);
        $expected = new ImportMapping(
            $event->reveal(),
            'my unique title',
            ['title' => 'abcdef', 'name' => 'azerty'],
            $date
        );
        $repository->add($expected)->shouldBeCalled();
        $repository->hasExistingMappingWithTitle($event->reveal(), 'my unique title')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $command = new CreateMapping($event->reveal(), ['title' => 'abcdef', 'name' => 'azerty'], 'my unique title');
        $handler = new CreateMappingHandler(
            $repository->reveal(),
            $date
        );

        $handler->handle($command);
    }
}
