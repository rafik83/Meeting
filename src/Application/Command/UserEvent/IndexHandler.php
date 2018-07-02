<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\UserEvent;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;

class IndexHandler
{
    /** @var ElasticSearchPersisterInterface */
    private $elasticSearchPersister;

    /** @var UserEventViewRepositoryInterface */
    private $userEventViewRepository;

    public function __construct(
        ElasticSearchPersisterInterface $elasticSearchPersister,
        UserEventViewRepositoryInterface $userEventViewRepository
    ) {
        $this->elasticSearchPersister = $elasticSearchPersister;
        $this->userEventViewRepository = $userEventViewRepository;
    }

    /**
     * @param Index $command
     */
    public function handle(Index $command): void
    {
        if ($command->removeAllByEvent) {
            //            $sheetIds = $this->searchAdapter->getSheetIds($command->event, [], 'fr');
            //            $this->sheetIndexer->deleteSheets($sheetIds);
        }

        $userEventViews = $this->userEventViewRepository->getByEvent($command->event);

        foreach ($userEventViews as $userEventView) {
            $this->elasticSearchPersister->persist($userEventView->id, $userEventView);
        }
    }
}
