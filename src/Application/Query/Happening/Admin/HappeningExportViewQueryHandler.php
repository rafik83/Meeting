<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningException;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportView;
use Proximum\Vimeet\Application\View\Happening\Admin\SpeakerExportView;
use Proximum\Vimeet\Application\View\Happening\Admin\SpeakersExportListView;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningExportViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param HappeningExportViewQuery $query
     *
     * @return HappeningExportListView
     * @throws EmptyHappeningException
     */
    public function handle(HappeningExportViewQuery $query): HappeningExportListView
    {
        $locale = $query->locale;
        $happenings = $this->happeningRepository->findListByEvent($query->event, $locale);

        $happeningExportViews = [];
        foreach ($happenings as $happening) {
            $speakers = $happening->getSpeakers();

            $speakerViews = [];
            foreach ($speakers as $speaker) {
                $speakerViews[] = new SpeakerExportView(
                    $speaker->getName(),
                    $speaker->getPosition($locale),
                    $speaker->getOrganization(),
                    $this->getAbsoluteLogoPath($speaker),
                    $this->getAbsolutePhotoPath($speaker)
                );
            }

            $happeningExportViews[] = new HappeningExportView(
                $happening->getTitle($locale),
                $happening->getDescription($locale),
                $happening->getCategory()->getTitle($locale),
                $happening->getBegin()->format('d-m-Y H:i'),
                $happening->getEnd()->format('d-m-Y H:i'),
                new SpeakersExportListView($speakerViews)
            );
        }

        if (count($happeningExportViews) === 0) {
            throw new EmptyHappeningException();
        }

        return new HappeningExportListView($happeningExportViews);
    }

    private function getAbsoluteLogoPath(Speaker $speaker): ?string
    {
        if ($speaker->getLogo() === null) {
            return null;
        }

        return $this->eventUrlGenerator->generateBaseEventAbsoluteUrl($speaker->getEvent()).$speaker->getLogo();
    }

    private function getAbsolutePhotoPath(Speaker $speaker): ?string
    {
        if ($speaker->getPhoto() === null) {
            return null;
        }

        return $this->eventUrlGenerator->generateBaseEventAbsoluteUrl($speaker->getEvent()).$speaker->getPhoto();
    }
}
