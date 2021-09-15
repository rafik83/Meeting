<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;

class BooleanFiltersBuilder
{
    /**
     * @var BooleanTemplateFilterRepositoryInterface
     */
    private $booleanTemplateFilterRepository;

    /**
     * @param BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository
     */
    public function __construct(
        BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository
    ) {
        $this->booleanTemplateFilterRepository = $booleanTemplateFilterRepository;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getFilters(Event $event)
    {
        $booleanFilters  = $this->booleanTemplateFilterRepository->getByEvent($event);
        $filtersPrepared = [];

        /** @var BooleanTemplateFilter $filter */
        foreach ($booleanFilters as $filter) {
            $filtersPrepared[$filter->getTemplateKey()] = $filter->getLabel();
        }

        return $filtersPrepared;
    }
}
