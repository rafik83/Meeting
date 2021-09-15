<?php

namespace Proximum\Vimeet\Application\Query\Event\Filter;

use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Filter\FilledTemplateFilterRepositoryInterface;

class GetTemplateFiltersQueryHandler
{
    /** @var BooleanTemplateFilterRepositoryInterface */
    private $booleanTemplateFilterRepository;

    /** @var FilledTemplateFilterRepositoryInterface */
    private $filledTemplateFilterRepository;

    public function __construct(
        BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository,
        FilledTemplateFilterRepositoryInterface $filledTemplateFilterRepository
    ) {
        $this->booleanTemplateFilterRepository = $booleanTemplateFilterRepository;
        $this->filledTemplateFilterRepository = $filledTemplateFilterRepository;
    }

    public function handle(GetTemplateFiltersQuery $query): array
    {
        return array_merge(
            $this->booleanTemplateFilterRepository->getByEventIdAndInformationType($query->event->getId(), $query->informationType),
            $this->filledTemplateFilterRepository->getByEventIdAndInformationType($query->event->getId(), $query->informationType)
        );
    }
}
