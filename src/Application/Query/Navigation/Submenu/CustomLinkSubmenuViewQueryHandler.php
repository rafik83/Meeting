<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Adapter\UriTemplateInterface;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as TypeExtraData;

class CustomLinkSubmenuViewQueryHandler
{
    private const PLACEHOLDER_USER_ID = 'userId';
    private const PLACEHOLDER_USER_EMAIL = 'userEmail';
    private const PLACEHOLDER_PARTICIPANT_ID = 'participantId';
    private const PLACEHOLDER_SHEET_ID = 'sheetId';
    private const PLACEHOLDER_TECH_EVENT_ID_CONTACT = 'techEventIdContact';

    private ExtraDataRepositoryInterface $extraDataRepository;

    private CustomLinkRepositoryInterface $customLinkRepository;

    private UriTemplateInterface $uriTemplate;

    public function __construct(
        CustomLinkRepositoryInterface $customLinkRepository,
        UriTemplateInterface $uriTemplate,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->customLinkRepository = $customLinkRepository;
        $this->uriTemplate = $uriTemplate;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(CustomLinkSubmenuViewQuery $query): ?array
    {

        $customLinks = $this->customLinkRepository->findByType(
            $query->sheet->getType()
        );

        $needParticipantId = false;
        $needTechEventIdContact = false;

        foreach ($customLinks as $customLink) {
            $needParticipantId = $needParticipantId || $this->urlContainsPlaceholder(
                    $customLink->getUrl($query->locale),
                    self::PLACEHOLDER_PARTICIPANT_ID
                );
            $needTechEventIdContact = $needTechEventIdContact || $this->urlContainsPlaceholder(
                    $customLink->getUrl($query->locale),
                    self::PLACEHOLDER_TECH_EVENT_ID_CONTACT
                );
        }

        $participant = $needParticipantId ? $query->sheet->getUserParticipant($query->user) : null;

        $techEventIdContact = $needTechEventIdContact ? $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            TypeExtraData::TECH_EVENT_IDENTIFIER_MD5,
            $query->user
        ) : null;

        $values = [
            self::PLACEHOLDER_USER_ID => urlencode($query->user->getId()),
            self::PLACEHOLDER_USER_EMAIL => urlencode($query->user->getEmail()),
            self::PLACEHOLDER_PARTICIPANT_ID => $participant ? $participant->getId() : null,
            self::PLACEHOLDER_SHEET_ID => $query->sheet->getId(),
            self::PLACEHOLDER_TECH_EVENT_ID_CONTACT => $techEventIdContact ? $techEventIdContact->getValue() : null,
        ];

        $submenuButtonViews = [];
        foreach ($customLinks as $customLink) {

            $needParticipantId = $this->urlContainsPlaceholder(
                $customLink->getUrl($query->locale),
                self::PLACEHOLDER_PARTICIPANT_ID
            );

            if ($needParticipantId && $participant === null) {
                continue;
            }

            $needTechEventIdContact = $this->urlContainsPlaceholder(
                $customLink->getUrl($query->locale),
                self::PLACEHOLDER_TECH_EVENT_ID_CONTACT
            );

            if ($needTechEventIdContact && $techEventIdContact === null) {
                continue;
            }

            $locale = $query->locale;
            $submenuButtonViews[] = new SubmenuButtonView(
                $customLink->getIconName(),
                $customLink->getLabel($locale),
                $this->uriTemplate->render($customLink->getUrl($query->locale), $values),
                false,
                null,
                false,
                [],
                $customLink->getIconColor(),
                $customLink->getLabelColor(),
                $customLink->getButtonColor(),

            );
        }

        return $submenuButtonViews;
    }

    private function urlContainsPlaceholder(string $link, string $placeholder): bool
    {
        return strpos($link, '{' . $placeholder . '}') !== false;
    }
}
