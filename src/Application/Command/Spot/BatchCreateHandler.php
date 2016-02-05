<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Components\Spot\ReferenceFactory;

class BatchCreateHandler
{
    /**
     * @var CreateHandler
     */
    private $createHandler;

    /**
     * @var ReferenceFactory
     */
    private $referenceFactory;

    /**
     * BatchCreateHandler constructor.
     *
     * @param CreateHandler    $createHandler
     * @param ReferenceFactory $referenceFactory
     */
    public function __construct(CreateHandler $createHandler, ReferenceFactory $referenceFactory)
    {
        $this->createHandler    = $createHandler;
        $this->referenceFactory = $referenceFactory;
    }

    /**
     * @param BatchCreate $batchCreate
     */
    public function handle(BatchCreate $batchCreate)
    {
        $references = $this->referenceFactory->createFromRecipes($batchCreate->recipes);

        foreach ($references as $reference) {
            $create                  = new Create($batchCreate->event);
            $create->reference       = $reference;
            $create->size            = $batchCreate->size;
            $create->meetingCapacity = $batchCreate->meetingCapacity;
            $create->seatCapacity    = $batchCreate->seatCapacity;
            $create->active          = $batchCreate->active;

            $this->createHandler->handle($create);
        }
    }
}
