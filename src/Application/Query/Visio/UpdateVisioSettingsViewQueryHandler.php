<?php

namespace Proximum\Vimeet\Application\Query\Visio;

use Proximum\Vimeet\Application\View\Visio\UpdateVisioSettingsLocalizedView;
use Proximum\Vimeet\Application\View\Visio\UpdateVisioSettingsView;

class UpdateVisioSettingsViewQueryHandler
{
    public function handle(UpdateVisioSettingsViewQuery $query): UpdateVisioSettingsView
    {
        $visioSettingsLocalizeViews = [];

        foreach ($query->event->getLocales() as $locale) {
            $visioSettingsLocalizeViews[$locale] = new UpdateVisioSettingsLocalizedView(
                $locale,
                $query->visioSettings->getHeader($locale),
                $query->visioSettings->getEndSound($locale),
                $query->visioSettings->getEndImage($locale),
                $query->visioSettings->getEndMessage($locale)
            );
        }

        return new UpdateVisioSettingsView($visioSettingsLocalizeViews);
    }
}
