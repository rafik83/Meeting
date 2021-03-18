<?php

namespace Proximum\Vimeet\Application\Command\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventIdsByEventInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;

class IndexHandler
{
    /** @var ElasticSearchPersisterInterface */
    private $elasticSearchPersister;

    /** @var GetUserEventIdsByEventInterface */
    private $getUserEventIdsByEvent;

    /** @var UserEventViewsFactory */
    private $userEventViewsFactory;

    public function __construct(
        ElasticSearchPersisterInterface $elasticSearchPersister,
        GetUserEventIdsByEventInterface $getUserEventIdsByEvent,
        UserEventViewsFactory $userEventViewsFactory
    ) {
        $this->elasticSearchPersister = $elasticSearchPersister;
        $this->getUserEventIdsByEvent = $getUserEventIdsByEvent;
        $this->userEventViewsFactory = $userEventViewsFactory;
    }

    /**
     * @param Index $command
     */
    public function handle(Index $command): void
    {
        if ($command->removeAllByEvent) {
            $this->elasticSearchPersister->deleteIds(
                TypesMapping::getTypeByClass(UserEventView::class),
                $this->getUserEventIdsByEvent->handle($command->event)
            );
        }

        $this->elasticSearchPersister->persist('uid', $this->userEventViewsFactory->getByEvent($command->event));
    }
}
