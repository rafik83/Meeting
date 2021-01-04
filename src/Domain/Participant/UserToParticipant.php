<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class UserToParticipant
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var \DateTimeInterface */
    private $date;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        TemplateDataFactory $templateDataFactory,
        \DateTimeInterface $date
    ) {
        $this->participantRepository = $participantRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->date = $date;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return Participant
     */
    public function handle(Sheet $sheet, User $user)
    {
        $participant = new Participant(
            $sheet,
            $user,
            $this->getTemplateData($sheet, $user),
            true,
            $this->date
        );
        $this->participantRepository->add($participant);

        $sheet->addParticipant($participant);

        return $participant;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return array
     */
    private function getTemplateData(Sheet $sheet, User $user)
    {
        $templateData = $this->templateDataFactory->createRegistrationFromType(
            $sheet->getType(),
            $sheet->getEvent()->getFallback()
        );

        $templateData->setTaggedData(
            [
                Tag::PARTICIPANT_FIRSTNAME => $user->getFirstName(),
                Tag::PARTICIPANT_LASTNAME  => $user->getLastName(),
                Tag::PARTICIPANT_AVATAR    => $user->getAvatar(),
                Tag::PARTICIPANT_POSITION  => $user->getPosition(),
                Tag::PARTICIPANT_PHONE     => $user->getPhone(),
                Tag::PARTICIPANT_MOBILE    => $user->getMobile(),
                Tag::PARTICIPANT_ADDRESS   => $user->getAddress(),
                Tag::PARTICIPANT_ZIPCODE   => $user->getZipCode(),
                Tag::PARTICIPANT_CITY      => $user->getCity(),
                Tag::PARTICIPANT_COUNTRY   => $user->getCountry(),
                Tag::PARTICIPANT_WEBSITE   => $user->getWebsite(),
                Tag::PARTICIPANT_GENDER    => $user->getGender(),
            ]
        );

        return $templateData->getData();
    }
}
