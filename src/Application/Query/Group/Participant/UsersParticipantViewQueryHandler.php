<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UsersParticipantViewQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param UsersParticipantViewQuery $participantViewQuery
     *
     * @return UserParticipantView[]
     */
    public function handle(UsersParticipantViewQuery $participantViewQuery)
    {
        $participants = $this->participantRepository->findByGroup($participantViewQuery->group);

        /** @var UserParticipantView[] $userParticipantViews */
        $userParticipantViews = [];

        foreach ($participants as $participant) {
            $user = $participant->getUser();
            $userId = $user->getId();

            if (!isset($userParticipantViews[$userId])) {
                $userParticipantViews[$userId] = new UserParticipantView(
                    $userId,
                    $user->getEmail(),
                    $user->getFullname(),
                    [$participant->getSheet()]
                );
            } else {
                $userParticipantViews[$userId]->addSheet($participant->getSheet());
            }
        }

        return $userParticipantViews;
    }
}
