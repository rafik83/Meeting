<?php

namespace Proximum\Vimeet\Application\View\Meeting;

class StateListsView
{
    /**
     * @var StateListView[]
     */
    private $stateListViews;

    /**
     * @return StateListView[]
     */
    public function getStateListViews()
    {
        return $this->stateListViews;
    }

    /**
     * @param $state
     *
     * @return null|StateListView
     */
    public function getByState($state)
    {
        foreach ($this->stateListViews as $stateListView) {
            if ($stateListView->state === $state) {
                return $stateListView;
            }
        }

        return null;
    }

    /**
     * @param StateListView $stateListView
     */
    public function addStateListView(StateListView $stateListView)
    {
        $this->stateListViews[] = $stateListView;
    }
}
