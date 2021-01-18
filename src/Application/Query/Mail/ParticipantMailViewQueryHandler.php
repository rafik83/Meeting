<?php

namespace Proximum\Vimeet\Application\Query\Mail;

use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantMailViewQueryHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ParticipantMailViewQueryHandler constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->participantRepository  = $participantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param ParticipantMailViewQuery $query
     *
     * @return ParticipantInfoView
     */
    public function handle(ParticipantMailViewQuery $query)
    {
        if (null === $query->sheet) {
            return $this->userInfoView($query->user);
        }

        $participant = $this->participantRepository->getParticipantForUserAndSheet($query->user, $query->sheet);

        if (null === $participant) {
            return $this->userInfoView($query->user);
        }

        $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
            $participant,
            $participant->getLocale()
        );

        $lastname = $this->participantInfoGuesser->guessParticipantLastName(
            $participant,
            $participant->getLocale()
        );

        $locale = $participant->getSheet()->getEvent()->getAvailableLocale($participant->getLocale());

        return new ParticipantInfoView(
            null !== $firstname ? $firstname : $query->user->getAccount()->getFirstName(),
            null !== $lastname ? $lastname : $query->user->getAccount()->getLastName(),
            $participant->getSheet()->getType()->getTitle($locale)
        );
    }

    /**
     * @param User $user
     *
     * @return ParticipantInfoView
     */
    private function userInfoView(User $user)
    {
        return new ParticipantInfoView(
            $user->getAccount()->getFirstName(),
            $user->getAccount()->getLastName()
        );
    }
}
