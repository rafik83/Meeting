<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Spot;

use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Proximum\Vimeet\Application\Command\Spot\BatchCreateHandler;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Components\Spot\Recipe;
use Proximum\Vimeet\Application\Components\Spot\ReferenceFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class BatchCreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event            = new Event();
        $recipe           = new Recipe('A', 1, 5);
        $referenceFactory = new ReferenceFactory();
        $spot1            = new Spot('A1', $event, 3, 1, 2, true);
        $spot2            = new Spot('A2', $event, 3, 1, 2, true);
        $spot3            = new Spot('A3', $event, 3, 1, 2, true);
        $spot4            = new Spot('A4', $event, 3, 1, 2, true);
        $spot5            = new Spot('A5', $event, 3, 1, 2, true);
        $recipes          = $referenceFactory->createFromRecipe($recipe);

        //Command
        $command = new BatchCreate($event);
        $command->recipes = $recipes;
        $command->size = 3;
        $command->active = true;
        $command->seatCapacity = 2;
        $command->meetingCapacity = 1;

        //Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->add($spot1);
        $spotRepository->add($spot2);
        $spotRepository->add($spot3);
        $spotRepository->add($spot4);
        $spotRepository->add($spot5);

        //Handler
        $handle = new BatchCreateHandler();
        $handle->handle($command);
    }
}