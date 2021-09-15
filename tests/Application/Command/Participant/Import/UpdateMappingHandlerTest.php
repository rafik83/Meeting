<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Import;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\Import\UpdateMapping;
use Proximum\Vimeet\Application\Command\Participant\Import\UpdateMappingHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;

class UpdateMappingHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $repository = $this->prophesize(ImportMappingRepositoryInterface::class);
        $date = new \DateTime();
        $event = $this->prophesize(Event::class);

        $importMapping = new ImportMapping(
            $event->reveal(),
            'title',
            ['previous' => 'mapping'],
            new \DateTime('2018-10-10 10:00:00.000')
        );

        $newMapping = ['new' => 'mapping'];

        $expected = clone $importMapping;
        $expected->update($newMapping, $date);

        $repository->update($expected)->shouldBeCalled();

        $command = new UpdateMapping($importMapping, $newMapping);
        $handler = new UpdateMappingHandler(
            $repository->reveal(),
            $date
        );
        $handler->handle($command);
    }
}
