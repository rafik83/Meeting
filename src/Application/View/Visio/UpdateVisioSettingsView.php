<?php

namespace Proximum\Vimeet\Application\View\Visio;

class UpdateVisioSettingsView
{
    /** @var UpdateVisioSettingsLocalizedView[] */
    public $updateVisioSettingsLocalizedViews;

    public function __construct(
        array $updateVisioSettingsLocalizedViews
    ) {
        $this->updateVisioSettingsLocalizedViews = $updateVisioSettingsLocalizedViews;
    }
}
