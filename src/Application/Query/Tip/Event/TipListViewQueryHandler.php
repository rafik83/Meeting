<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\NoTipAvailableException;
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipListViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * TipListViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param TipListViewQuery $query
     *
     * @throws NoTipAvailableException
     *
     * @return TipView[]
     */
    public function handle(TipListViewQuery $query)
    {
        $tips = $this->tipRepository->getAll();

        if (empty($tips)) {
            throw new NoTipAvailableException();
        }

        $tipViews = [];

        foreach ($tips as $tip) {
            $tipViews[] = new TipView($tip->getId(), $tip->getTitle(), $query->locale);
        }

        return $tipViews;
    }
}
