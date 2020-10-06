<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\Participant\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfos;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfosHandler;
use Proximum\Vimeet\Application\View\Happening\Webinar\SpeakerWebinarView;
use Proximum\Vimeet\Application\View\Happening\Webinar\ViewerWebinarView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Application\View\Happening\Webinar\WebinarView;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordingAllowed;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandler
{
    /** @var GetUserParticipantInfosHandler */
    private $getUserParticipantInfosHandler;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var DateTimeInterface */
    private $dateTime;

    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var IsRecordingAllowed */
    private $isRecordingAllowed;

    /** @var HappeningBroadcastRepositoryInterface */
    private $happeningBroadcastRepository;

    public function __construct(
        GetUserParticipantInfosHandler $getUserParticipantInfosHandler,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        HappeningBroadcastRepositoryInterface $happeningBroadcastRepository,
        IsRecordingAllowed $isRecordingAllowed,
        \DateTimeInterface $dateTime
    ) {
        $this->getUserParticipantInfosHandler = $getUserParticipantInfosHandler;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->dateTime = $dateTime;
        $this->isRecordingAllowed = $isRecordingAllowed;
        $this->happeningBroadcastRepository = $happeningBroadcastRepository;
    }

    public function handle(GetWebinarViewQuery $query): WebinarView
    {
        $happening = $query->getHappening();
        $isSpeaker = $happening->isInteractiveWebinar() || $happening->hasSpeaker($query->getUser());

        $sessionAndTokenView = $this->getSessionAndToken($happening, $isSpeaker);
        $timeRemainingInSeconds = max(
            0,
            $happening->getEnd()->getTimestamp() - $this->dateTime->getTimestamp()
        );
        $timeRemainingBeforeStartInSeconds = max(
            0,
            $happening->getBegin()->getTimestamp() - $this->dateTime->getTimestamp()
        );

        $liveUrl = $this->getLiveUrl($happening, $query->getUser());

        if ($isSpeaker) {
            return new SpeakerWebinarView(
                $happening->getId(),
                $query->getUser()->getId(),
                $happening->getTitle($query->getLocale()),
                $happening->isVideoWebinarAndHasLiveUrl(),
                $sessionAndTokenView->token,
                $sessionAndTokenView->sessionId,
                $sessionAndTokenView->apiKey,
                $this->getSpeakerViews($happening, $query->getLocale()),
                $this->getParticipantViews($happening, $query->getLocale()),
                new TimeRangeView($happening->getBegin(), $happening->getEnd()),
                $this->dateTime,
                $timeRemainingInSeconds,
                round($timeRemainingInSeconds * 0.2),
                $timeRemainingBeforeStartInSeconds,
                $happening->getEnd()->getTimestamp() + 60*15,
                $happening->getWebinarHeaderImage($query->getLocale()),
                $liveUrl,
                $happening->isSidebarAllowed(),
                $this->isVideoWebinarAndHappeningIsEnded($happening),
                $this->isRecordingAllowed->isSatisfiedBy($happening),
                $this->isWebinarRecording($happening),
                $happening->getEvent()->getAutoArchiveWebinar(),
                $happening->allowWebinarOnHLS()
            );
        }

        return new ViewerWebinarView(
            $happening->getId(),
            $query->getUser()->getId(),
            $happening->getTitle($query->getLocale()),
            $happening->isVideoWebinarAndHasLiveUrl(),
            $sessionAndTokenView->token,
            $sessionAndTokenView->sessionId,
            $sessionAndTokenView->apiKey,
            $this->getSpeakerViews($happening, $query->getLocale()),
            $this->getParticipantViews($happening, $query->getLocale()),
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            $this->dateTime,
            $timeRemainingInSeconds,
            $happening->getWebinarHeaderImage($query->getLocale()),
            $liveUrl,
            $happening->isSidebarAllowed(),
            $this->isVideoWebinarAndHappeningIsEnded($happening),
            $this->getHLSUrl($happening)
        );
    }

    private function getHLSUrl(Happening $happening): ?string
    {
        if (false === $happening->allowWebinarOnHLS()) {
            return null;
        }

        $broadcast = $this->happeningBroadcastRepository->getByHappening($happening);

        if (null === $broadcast || $broadcast->isStopped()) {
            return null;
        }

        return $broadcast->getHlsUrl();
    }

    private function getLiveUrl(Happening $happening, User $user): ?string
    {
        $liveUrl = $happening->getLiveUrl();

        if (strpos($liveUrl, '_firstname_') !== false || strpos($liveUrl, '_lastname_') !== false) {
            $placeholders = ['_firstname_','_lastname_'];
            $values = [urlencode($user->getFirstName()), urlencode($user->getLastName())];
            $liveUrl = str_replace($placeholders, $values, $happening->getLiveUrl());
        }

        return $liveUrl;
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

    private function isWebinarRecording(Happening $happening): bool
    {
        if (!$happening->isWebinarRecorded()) {
            return false;
        }

        return $this->recordArchiveRepository->hasStartedRecordArchiveForHappening($happening);
    }
}
