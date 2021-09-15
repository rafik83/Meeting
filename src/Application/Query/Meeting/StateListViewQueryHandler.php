<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\View\Meeting\StateListsView;
use Proximum\Vimeet\Application\View\Meeting\StateListView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class StateListViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * StateListViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param StateListViewQuery $query
     *
     * @return StateListsView
     */
    public function handle(StateListViewQuery $query)
    {
        $lists = new StateListsView();

        foreach (Meeting\Constant::getAllStates() as $state) {
            $count = $this->requestRepository->countSheetState(
                $query->sheet,
                array_merge($query->filters, ['state' => $state]),
                $query->slotsToFilter
            );

            $lists->addStateListView(new StateListView(
                $state,
                $count
            ));
        }

        return $lists;
    }
}
