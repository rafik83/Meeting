<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\View\Happening\HappeningView;

class HappeningViewQueryHandler
{
    /** @var SpeakerViewQueryHandler */
    private $speakerViewQueryHandler;

    /** @var CategoryViewQueryHandler */
    private $categoryViewQueryHandler;

    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    public function __construct(
        SpeakerViewQueryHandler $speakerViewQueryHandler,
        CategoryViewQueryHandler $categoryViewQueryHandler,
        CanAccessToWebinar $canAccessToWebinar
    ) {
        $this->speakerViewQueryHandler = $speakerViewQueryHandler;
        $this->categoryViewQueryHandler = $categoryViewQueryHandler;
        $this->canAccessToWebinar = $canAccessToWebinar;
    }

    public function handle(HappeningViewQuery $query): HappeningView
    {
        $happening = $query->happening;
        $user = $query->user;

        $happeningCategoryView = $this->categoryViewQueryHandler->handle(
            new CategoryViewQuery($happening, $query->locale)
        );

        $speakerView = $this->speakerViewQueryHandler->handle(
            new SpeakerViewQuery($happening, $query->locale)
        );

        return new HappeningView(
            $happening->getId(),
            $happeningCategoryView,
            $happening->getBegin(),
            $happening->getEnd(),
            $happening->getTitle($query->locale),
            $happening->getDescription($query->locale),
            null, // Happening Picture
            $speakerView,
            $query->event->getTimeZone(),
            $happening->getLimitParticipant(),
            false,
            $happening->isWebinar() && $this->canAccessToWebinar->isSatisfiableBy($happening, $user)
        );
    }
}
