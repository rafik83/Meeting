<?php

namespace Proximum\Vimeet\Application\View\Visio;

class UpdateVisioSettingsView
{
    /** @var UpdateVisioSettingsLocalizedView[] */
    public $updateVisioSettingsLocalizedViews;

    /**
     * @param UpdateVisioSettingsLocalizedView[] $updateVisioSettingsLocalizedViews
     */
    public function __construct(
        array $updateVisioSettingsLocalizedViews
    ) {
        $this->updateVisioSettingsLocalizedViews = $updateVisioSettingsLocalizedViews;
    }
}
