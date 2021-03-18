<?php

namespace Proximum\Vimeet\Domain\Event\Tip;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class Duplicator
{
    /**
     * @var TipRepositoryInterface
     */
    private $tipRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param TipRepositoryInterface $tipRepository
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->tipRepository = $tipRepository;
        $this->dateTime      = $dateTime;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage)
    {
        $tips = $this->tipRepository->getByEvent($event->getDuplicatedFrom());

        foreach ($tips as $tip) {
            $newTip = new Tip(
                $tip->getTitle(),
                $event,
                $tip->isOnMeetingManagement(),
                $tip->isOnCatalog(),
                $tip->isOnPrintPlanning(),
                $tip->isOnSheet(),
                $tip->isOnAgenda(),
                $tip->isOnPackage(),
                $tip->isOnContacts(),
                $tip->isOnProgram(),
                $tip->isOnConfirmationPhone(),
                $tip->isOnNetworking(),
                $this->dateTime
            );

            foreach ($tip->getTypes() as $type) {
                $newTip->addType($duplicatorDataStorage->types[$type->getId()]);
            }

            foreach ($event->getLocales() as $locale) {
                $newTip->translate(
                    $locale,
                    $tip->getTranslationTitle($locale),
                    $tip->getTranslationContent($locale),
                    $this->dateTime
                );
            }

            $this->tipRepository->add($newTip);
        }
    }
}
