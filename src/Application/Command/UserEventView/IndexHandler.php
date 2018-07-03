<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;

class IndexHandler
{
    /** @var ElasticSearchPersisterInterface */
    private $elasticSearchPersister;

    /** @var UserEventViewsFactory */
    private $userEventViewsFactory;

    public function __construct(
        ElasticSearchPersisterInterface $elasticSearchPersister,
        UserEventViewsFactory $userEventViewsFactory
    ) {
        $this->elasticSearchPersister = $elasticSearchPersister;
        $this->userEventViewsFactory = $userEventViewsFactory;
    }

    /**
     * @param Index $command
     */
    public function handle(Index $command): void
    {
        if ($command->removeAllByEvent) {
//            $userEventDocumentIds = $this->searchAdapter->getUserEventByEvent($command->event, [], 'fr');
//            $this->userEventIndexer->delete($userEventDocumentIds);
        }

        $userEventViews = $this->userEventViewsFactory->getByEvent($command->event);

        foreach ($userEventViews as $userEventView) {
            $this->elasticSearchPersister->persist($userEventView->id, $userEventView);
        }
    }
}
