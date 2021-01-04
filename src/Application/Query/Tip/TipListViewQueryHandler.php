<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\Exception\Tip\NoTipAvailableException;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
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
     * @return TipTranslationView[]
     */
    public function handle(TipListViewQuery $query)
    {
        $tipTranslationViews = $this->tipRepository->getTipTranslationViewByLocale($query->locale);

        if (empty($tipTranslationViews)) {
            throw new NoTipAvailableException();
        }

        return $tipTranslationViews;
    }
}
