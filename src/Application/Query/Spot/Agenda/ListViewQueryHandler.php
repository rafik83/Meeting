<?php

namespace Proximum\Vimeet\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Application\View\Spot\Agenda\ListView;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class ListViewQueryHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @var SpotViewQueryHandler
     */
    private $spotViewQueryHandler;

    /**
     * ListViewQueryHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     * @param SpotViewQueryHandler    $spotViewQueryHandler
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SpotViewQueryHandler $spotViewQueryHandler
    ) {
        $this->spotRepository       = $spotRepository;
        $this->spotViewQueryHandler = $spotViewQueryHandler;
    }

    /**
     * @param ListViewQuery $query
     *
     * @return ListView
     */
    public function handle(ListViewQuery $query)
    {
        $spots     = $this->spotRepository->getActiveByEvent($query->event);
        $spotViews = [];

        foreach ($spots as $spot) {
            $spotView    = $this->spotViewQueryHandler->handle(new SpotViewQuery($spot));
            $spotViews[] = $spotView;
        }

        return new ListView($spotViews);
    }
}
