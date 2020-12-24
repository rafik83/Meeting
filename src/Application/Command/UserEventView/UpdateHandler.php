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
use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;

class UpdateHandler
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

    public function handle(Update $command): void
    {
        $userEventViews = $this->userEventViewsFactory->getByEventAndUser($command->event, $command->user);

        if (empty($userEventViews)) {
            $this->elasticSearchPersister->deleteIds(
                TypesMapping::getTypeByClass(UserEventView::class),
                [UserEventView::generateId($command->event->getId(), $command->user->getId())]
            );

            return;
        }

        $this->elasticSearchPersister->persist('uid', $userEventViews);
    }
}
