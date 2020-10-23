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
    private const PLACEHOLDER_USER_ID = '%userId%';
    private const PLACEHOLDER_USER_EMAIL = '%userEmail%';
    private const PLACEHOLDER_PARTICIPANT_ID = '%participantId%';
    private const PLACEHOLDER_SHEET_ID = '%sheetId%';
    private const PLACEHOLDER_TECH_EVENT_ID_CONTACT = '%techEventIdContact%';

    public const USER_CTA_PLACEHOLDERS_ALLOWED = [
        self::PLACEHOLDER_USER_ID,
        self::PLACEHOLDER_USER_EMAIL,
        self::PLACEHOLDER_PARTICIPANT_ID,
        self::PLACEHOLDER_SHEET_ID,
        self::PLACEHOLDER_TECH_EVENT_ID_CONTACT,
    ];
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
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

        $needParticipantId = $this->urlContainsPlaceholder(
            $parameters['link'],
            self::PLACEHOLDER_PARTICIPANT_ID
        );
        $needTechEventIdContact = $this->urlContainsPlaceholder(
            $parameters['link'],
            self::PLACEHOLDER_TECH_EVENT_ID_CONTACT
        );

        $participant = $needParticipantId ? $query->sheet->getUserParticipant($query->user) : null;

        if ($needParticipantId && $participant === null) {
            return null;
        }

        $techEventIdContact = $needTechEventIdContact ? $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            TypeExtraData::TECH_EVENT_IDENTIFIER_MD5,
            $query->user
        ) : null;

        if ($needTechEventIdContact && $techEventIdContact === null) {
            return null;
        }

        $values = [
            urlencode($query->user->getId()),
            urlencode($query->user->getEmail()),
            $participant ? $participant->getId() : null,
            $query->sheet->getId(),
            $techEventIdContact ? $techEventIdContact->getValue() : null,
        ];
        $link = str_replace(self::USER_CTA_PLACEHOLDERS_ALLOWED, $values, $parameters['link']);

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

    private function urlContainsPlaceholder(string $link, string $placeholder): bool
    {
        return strpos($link, $placeholder) !== false;
    }
}
