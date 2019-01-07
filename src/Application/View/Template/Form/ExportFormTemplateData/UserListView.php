<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData;

class UserListView
{
    /** @var UserDataView[] */
    public $userViews;

    /** @var string[] indexed by object key */
    public $formTemplateObjectLabels;

    public function __construct(
        array $userViews,
        array $formTemplateObjectLabels = []
    ) {
        $this->userViews = $userViews;
        $this->formTemplateObjectLabels = $formTemplateObjectLabels;
    }
}
