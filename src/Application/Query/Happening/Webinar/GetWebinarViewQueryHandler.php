<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfos;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfosHandler;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandler
{
    /** @var GetUserParticipantInfosHandler */
    private $getUserParticipantInfosHandler;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        GetUserParticipantInfosHandler $getUserParticipantInfosHandler,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->getUserParticipantInfosHandler = $getUserParticipantInfosHandler;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->dateTime = $dateTime;
    }

    public function handle(GetWebinarViewQuery $query): WebinarView
    {
        $happening = $query->getHappening();
        $isSpeaker = $happening->isInteractiveWebinar() || $happening->hasSpeaker($query->getUser());

        $sessionAndTokenView = $this->getSessionAndToken($happening, $isSpeaker);
        $timeRemainingInSeconds = max(0, $happening->getEnd()->getTimestamp() - $this->dateTime->getTimestamp());

        return new WebinarView(
            $happening->getId(),
            $query->getUser()->getId(),
            $happening->getTitle($query->getLocale()),
            $happening->isVideoWebinarAndHasLiveUrl(),
            $sessionAndTokenView->token,
            $sessionAndTokenView->sessionId,
            $sessionAndTokenView->apiKey,
            $isSpeaker,
            $this->getSpeakerViews($happening, $query->getLocale()),
            $this->getParticipantViews($happening, $query->getLocale()),
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            $this->dateTime,
            $timeRemainingInSeconds,
            round($timeRemainingInSeconds * 0.2),
            $happening->getWebinarHeaderImage($query->getLocale()),
            $happening->getLiveUrl(),
            $this->isVideoWebinarAndHappeningIsEnded($happening),
            $happening->isWebinarRecorded()
        );
    }

    private function isVideoWebinarAndHappeningIsEnded(Happening $happening): bool
    {
         return $happening->isVideoWebinarAndHasLiveUrl()
            && $happening->getEnd() < $this->dateTime;
    }

    private function getSessionAndToken(Happening $happening, bool $isSpeaker): SessionAndTokenView
    {
        if ($this->isVideoWebinarAndHappeningIsEnded($happening)) {
            return new SessionAndTokenView();
        }

        if (!$happening->hasWebinarSessionId()) {
            throw new \LogicException('Happening webinar session id not created');
        }

        $session = $this->videoConferenceAdapter->getSession($happening->getWebinarSessionId());

        return new SessionAndTokenView(
            $session->getSessionId(),
            $this->videoConferenceAdapter->generateAccessToken(
                $session,
                $happening->getEnd(),
                [],
                $isSpeaker
            ),
            $this->videoConferenceAdapter->getApiKey()
        );
    }

    /**
     * @return WebinarParticipantView[]
     */
    private function getParticipantViews(Happening $happening, string $locale): array
    {
        if (!$happening->isInteractiveWebinar()) {
            return [];
        }

        $participantViews = [];

        foreach ($happening->getParticipations() as $happeningParticipation) {
            $user = $happeningParticipation->getUser();

            try {
                $participantView = $this->getUserParticipantInfosHandler->handle(
                    new GetUserParticipantInfos($happening->getEvent(), $user, $locale)
                );
            } catch (ParticipantNotFoundException $participantNotFoundException) {
                continue;
            } catch (SheetNotFoundException $sheetNotFoundException) {
                continue;
            }

            $participantViews[] = new WebinarParticipantView(
                $user->getId(),
                $participantView->firstName,
                $participantView->lastName,
                $participantView->position,
                $participantView->getSheetTitle()
            );
        }

        return $participantViews;
    }

    /**
     * @return WebinarSpeakerView[]
     */
    private function getSpeakerViews(Happening $happening, string $locale): array
    {
        if ($this->isVideoWebinarAndHappeningIsEnded($happening)) {
            return [];
        }

        $speakerViews = [];

        foreach ($happening->getSpeakers() as $speaker) {
            if (!$speaker->getUser()) {
                continue;
            }

            $speakerViews[] = new WebinarSpeakerView(
                $speaker->getUser()->getId(),
                $speaker->getFirstname(),
                $speaker->getLastname(),
                $speaker->getPosition($locale),
                $speaker->getOrganization()
            );
        }

        return $speakerViews;
    }
}
