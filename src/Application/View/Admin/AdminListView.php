<?php

namespace Proximum\Vimeet\Application\View\Admin;

class AdminListView
{
    /** @var AdminView[] */
    public $adminViews;

    public function __construct(array $adminViews = [])
    {
        $this->adminViews = $adminViews;
    }
}
