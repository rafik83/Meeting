<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Components\Spot\ReferenceFactory;
use Proximum\Vimeet\Application\Exception\Spot\MultipleUniqueReferenceViolationException;
use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;

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
     *
     * @throws MultipleUniqueReferenceViolationException
     */
    public function handle(BatchCreate $batchCreate)
    {
        $references = $this->referenceFactory->createFromRecipes($batchCreate->recipes);
        $duplicates = [];

        foreach ($references as $reference) {
            try {
                $create                  = new Create($batchCreate->event);
                $create->reference       = $reference;
                $create->size            = $batchCreate->size;
                $create->meetingCapacity = $batchCreate->meetingCapacity;
                $create->seatCapacity    = $batchCreate->seatCapacity;
                $create->active          = $batchCreate->active;
                $create->priority        = $batchCreate->priority;
                $create->visio           = $batchCreate->visio;

                $this->createHandler->handle($create);
            } catch (UniqueReferenceViolationException $exception) {
                $duplicates[] = $exception->getReference();
            }
        }

        if (!empty($duplicates)) {
            throw new MultipleUniqueReferenceViolationException($duplicates);
        }
    }
}
