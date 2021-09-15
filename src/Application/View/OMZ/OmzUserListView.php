<?php

namespace Proximum\Vimeet\Application\View\OMZ;

class OmzUserListView
{
    /** @var OmzUserView[] */
    public $userViews;

    /**
     * OmzUserListView constructor.
     *
     * @param OmzUserView[] $userViews
     */
    public function __construct(array $userViews)
    {
        $this->userViews = $userViews;
    }
}
