<?php

namespace Proximum\Vimeet\Application\Components\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningListViewFactory
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * HappeningListViewFactory constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return HappeningListView[]
     */
    public function getListByEventAndLocale(Event $event, $locale)
    {
        // Get happenings
        $happenings = $this->happeningRepository->findListByEvent($event, $locale);

        return $this->createFromHappenings($happenings, $locale);
    }

    /**
     * @param Speaker $speaker
     * @param string  $locale
     *
     * @return HappeningListView[]
     */
    public function getListBySpeakerAndLocale(Speaker $speaker, $locale)
    {
        // Get happenings
        $happenings = $this->happeningRepository->findBySpeaker($speaker, $locale);

        return $this->createFromHappenings($happenings, $locale);
    }

    /**
     * @param array  $happenings
     * @param string $locale
     *
     * @return array
     */
    private function createFromHappenings(array $happenings, $locale)
    {
        return array_map(function (Happening $happening) use ($locale) {
            return $this->createFromHappening($happening, $locale);
        }, $happenings);
    }

    /**
     * @param Happening $happening
     * @param string    $locale
     *
     * @return HappeningListView
     */
    private function createFromHappening(Happening $happening, $locale)
    {
        return new HappeningListView(
            $happening->getId(),
            $happening->getCategory()->getTitle($locale),
            $happening->getBegin(),
            $happening->getEnd(),
            $happening->getTitle($locale),
            $happening->isQuestionAllowed(),
            array_map(
                function (Speaker $speaker) {
                    return $speaker->getName();
                },
                $happening->getSpeakers()
            ),
            true
        );
    }
}
