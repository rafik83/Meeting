<?php

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Application\View\Spot\ListView;
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
     * @param SpotRepositoryInterface $spotRepository
     * @param SpotViewQueryHandler    $spotViewQueryHandler
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SpotViewQueryHandler $spotViewQueryHandler
    ) {
        $this->spotRepository = $spotRepository;
        $this->spotViewQueryHandler = $spotViewQueryHandler;
    }

    /**
     * @param ListViewQuery $query
     *
     * @return ListView
     */
    public function handle(ListViewQuery $query)
    {
        $listView = new ListView();
        $spots = $this->spotRepository->getSpotFilter($query->event, $query->filters);

        foreach ($spots as $spot) {
            $listView->addSpot($this->spotViewQueryHandler->handle(new SpotViewQuery($spot, $query->locale)));
        }

        return $listView;
    }
}
