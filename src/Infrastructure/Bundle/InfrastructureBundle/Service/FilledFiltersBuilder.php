<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;
use Proximum\Vimeet\Domain\Repository\Filter\FilledTemplateFilterRepositoryInterface;

class FilledFiltersBuilder
{
    /** @var FilledTemplateFilterRepositoryInterface */
    private $filledTemplateFilterRepository;

    public function __construct(FilledTemplateFilterRepositoryInterface $filledTemplateFilterRepository)
    {
        $this->filledTemplateFilterRepository = $filledTemplateFilterRepository;
    }

    public function getFilters(Event $event): array
    {
        $filledFilters = $this->filledTemplateFilterRepository->getByEvent($event);
        $filtersPrepared = [];

        /** @var FilledTemplateFilter $filter */
        foreach ($filledFilters as $filter) {
            $filtersPrepared[$filter->getTemplateKey()] = $filter->getLabel();
        }

        return $filtersPrepared;
    }
}
