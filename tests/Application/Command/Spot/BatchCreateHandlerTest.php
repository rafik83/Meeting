<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Proximum\Vimeet\Application\Command\Spot\BatchCreateHandler;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\CreateHandler;
use Proximum\Vimeet\Application\Components\Spot\Recipe;
use Proximum\Vimeet\Application\Components\Spot\ReferenceFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchCreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event   = EventFactory::createEvent();
        $recipes = [new Recipe('A', 1, 3), new Recipe('B', 1, 3)];

        //Command
        $command                  = new BatchCreate($event);
        $command->recipes         = $recipes;
        $command->size            = 3;
        $command->active          = true;
        $command->seatCapacity    = 2;
        $command->meetingCapacity = 1;
        $command->priority        = 10;
        $command->visio           = true;

        //Mock
        $referenceFactory = $this->prophesize(ReferenceFactory::class);
        $referenceFactory->createFromRecipes($recipes)->shouldBeCalled()->willReturn([
            'A1', 'A2', 'A3', 'B1', 'B2', 'B3',
        ]);

        $createHandler = $this->prophesize(CreateHandler::class);
        $createHandler->handle(Argument::that(function (Create $create) {
            return
                in_array($create->reference, ['A1', 'A2', 'A3', 'B1', 'B2', 'B3']) &&
                3 === $create->size &&
                1 === $create->meetingCapacity &&
                2 === $create->seatCapacity &&
                true === $create->active &&
                true === $create->visio &&
                10 === $create->priority;
        }))->shouldBeCalled();

        //Handler
        $handle = new BatchCreateHandler($createHandler->reveal(), $referenceFactory->reveal());
        $handle->handle($command);
    }
}
