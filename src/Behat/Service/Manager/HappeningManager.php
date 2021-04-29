<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Happening\HappeningTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningManager
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
    }

    public function createHappening(Happening\Category $category): Happening
    {
        $happening = new Happening(
            $category->getEvent(),
            new \DateTime('2020-09-01 10:00:00'),
            new \DateTime('2020-09-01 11:00:00'),
            $category,
            [],
            true,
            null,
            null,
            true
        );

        $this->happeningRepository->add($happening);

        $happeningTranslation = new HappeningTranslation($happening, 'fr', 'Présentation flash', 'Description de la conférence');
        $happening->setTranslation($happeningTranslation);

        return $happening;
    }

    public function userParticipateToHappening(User $user, Happening $happening): void
    {
        $this->happeningParticipationRepository->add(new HappeningParticipation($happening, $user));
    }

    public function allowTypeToAccessHappening(Type $type, Happening $happening): void
    {
        $happening->update(
            $happening->getBegin(),
            $happening->getEnd(),
            $happening->getCategory(),
            [$type],
            $happening->isQuestionAllowed(),
            $happening->getLimitParticipant(),
            $happening->isWebinar(),
            $happening->isInteractiveWebinar(),
            $happening->isVideoWebinar(),
            $happening->getInvitationCode(),
            $happening->getLiveUrl(),
            $happening->isSidebarAllowed(),
            $happening->isWebinarRecorded(),
            $happening->allowWebinarOnHLS(),
            $happening->isWebinarRecordSentToSpeakers()
        );
        $this->happeningRepository->set($happening);
    }
}
