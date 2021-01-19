<?php

namespace Proximum\Vimeet\Application\Query\Scan\Happening;

use Proximum\Vimeet\Application\View\Scan\Happening\HappeningView;
use Proximum\Vimeet\Application\View\Scan\Happening\ListView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ListViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        $happenings = $this->happeningRepository->findByEvent($query->event);

        $locale = $query->event->getAvailableLocale($query->locale);

        $happeningViews = [];
        foreach ($happenings as $happening) {
            $happeningViews[] = new HappeningView(
                $happening->getId(),
                $happening->getTitle($locale),
                $happening->getBegin()
            );
        }

        return new ListView($happeningViews);
    }
}
