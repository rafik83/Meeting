<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as TypeExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class UserCtaSubmenuViewQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    )
    {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(UserCtaSubmenuViewQuery $query): ?SubmenuButtonView
    {
        $customUserIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $query->event,
            Type::TYPE_CUSTOM_BUTTON
        );

        if (null === $customUserIdExtraParameter) {
            return null;
        }

        $parameters = json_decode($customUserIdExtraParameter->getValue(), true);

        if (isset($parameters['concerned_type_ids'])
            && !in_array($query->sheet->getType()->getId(), $parameters['concerned_type_ids'], false)
        ) {
            return null;
        }

        $needParticipantId = strpos($parameters['link'], '%participantId%') !== false;

        $participant = $needParticipantId ? $query->sheet->getUserParticipant($query->user) : null;

        if ($needParticipantId && $participant === null) {
            return null;
        }

        $needTechEventIdContact = strpos($parameters['link'], '%techEventIdContact%') !== false;

        $techEventIdContact = $needTechEventIdContact ? $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            TypeExtraData::IMPORTED_FROM_TECH_EVENT,
            $query->user
        ) : null;

        if ($needTechEventIdContact && $techEventIdContact === null) {
            return null;
        }

        $placeholders = ['%userId%', '%userEmail%', '%participantId%', '%techEventIdContact%'];
        $values = [
            urlencode($query->user->getId()),
            urlencode($query->user->getEmail()),
            $participant ? $participant->getId() : null,
            $techEventIdContact ? $techEventIdContact->getId() : null
        ];
        $link = str_replace($placeholders, $values, $parameters['link']);

        return new SubmenuButtonView(
            Category::CUSTOM_BUTTON_ICON,
            $parameters['button-label'][$query->locale] ?? '',
            $link,
            false,
            null,
            false,
            ['target' => '_blank']
        );
    }
}
