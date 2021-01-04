<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * TipViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param TipViewQuery $query
     *
     * @return PaginatedTipView
     */
    public function handle(TipViewQuery $query)
    {
        $tips = $this->tipRepository->paginate($query->page, $query->limit);

        return new PaginatedTipView($tips);
    }
}
