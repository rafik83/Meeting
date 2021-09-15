<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class PaginatedTipViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * PaginatedTipViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param PaginatedTipViewQuery $query
     *
     * @return PaginatedTipView
     */
    public function handle(PaginatedTipViewQuery $query)
    {
        $tips = $this->tipRepository->paginateByEvent($query->event, $query->page, $query->limit);

        return  new PaginatedTipView($tips);
    }
}
