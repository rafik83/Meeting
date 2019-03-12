<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningException;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportView;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningExportViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param HappeningExportViewQuery $query
     *
     * @return HappeningExportListView
     * @throws EmptyHappeningException
     */
    public function handle(HappeningExportViewQuery $query): HappeningExportListView
    {
        $locale = $query->locale;
        $happenings = $this->happeningRepository->findListByEvent($query->event, $locale);

        $happeningExportViews = [];
        foreach ($happenings as $happening) {
            $speakers = $happening->getSpeakers();

            $happeningExportViews[] = new HappeningExportView(
                $happening->getTitle($locale),
                $happening->getDescription($locale),
                $happening->getCategory()->getTitle($locale),
                $happening->getBegin()->format('d-m-Yh:i'),
                $happening->getEnd()->format('d-m-Yh:i')
            );
        }

        if (count($happeningExportViews) === 0) {
            throw new EmptyHappeningException();
        }

        return new HappeningExportListView($happeningExportViews);
    }
}
