<?php

namespace Proximum\Vimeet\Application\Query\Register;

use Proximum\Vimeet\Application\View\Register\PreFillUserDataView;
use Proximum\Vimeet\Domain\Account\EventParticipationPreFiller;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Event\LastEventParticipation;

class PreFillUserDataHandler
{
    /**
     * @var LastEventParticipation
     */
    private $lastEventParticipation;

    /**
     * @var EventParticipationPreFiller
     */
    private $eventParticipationPreFiller;

    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @param LastEventParticipation      $lastEventParticipation
     * @param EventParticipationPreFiller $eventParticipationPreFiller
     * @param Synchronizer                $accountSynchronizer
     */
    public function __construct(
        LastEventParticipation $lastEventParticipation,
        EventParticipationPreFiller $eventParticipationPreFiller,
        Synchronizer $accountSynchronizer
    ) {
        $this->lastEventParticipation = $lastEventParticipation;
        $this->eventParticipationPreFiller = $eventParticipationPreFiller;
        $this->accountSynchronizer = $accountSynchronizer;
    }

    /**
     * @param PreFillUserData $command
     *
     * @return PreFillUserDataView
     */
    public function handle(PreFillUserData $command): PreFillUserDataView
    {
        $lastParticipation = $this->lastEventParticipation->getLastEventParticipation($command->user, $command->event);

        if (null !== $lastParticipation) {
            $templateData = $this->eventParticipationPreFiller->preFillTemplate(
                $command->templateData,
                $lastParticipation,
                $command->locale
            );
        }

        $templateData = $this->accountSynchronizer->get(
            $templateData ?? $command->templateData,
            $command->user
        );

        return new PreFillUserDataView(
            $templateData,
            $lastParticipation ? $lastParticipation->getSheet()->getEvent() : null,
            null !== $lastParticipation
        );
    }
}
