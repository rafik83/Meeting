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

        if (!$happening->hasWebinarSessionId()) {
            throw new \LogicException('Happening webinar session id not created');
        }

        $session = $this->videoConferenceAdapter->getSession($happening->getWebinarSessionId());

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $happening->getEnd(),
            [],
            $isSpeaker
        );

        $sessionId = $session->getSessionId();
        $timeRemainingInSeconds = max(
            0,
            $happening->getEnd()->getTimestamp() - $this->dateTime->getTimestamp()
        );
        $timeRemainingBeforeStartInSeconds = max(
            0,
            $happening->getBegin()->getTimestamp() - $this->dateTime->getTimestamp()
        );

        $speakers = [];

        foreach ($happening->getSpeakers() as $speaker) {
            $speakers[] = new WebinarSpeakerView(
                $speaker->getUser()->getId(),
                $speaker->getFirstname(),
                $speaker->getLastname(),
                $speaker->getPosition($query->getLocale()),
                $speaker->getOrganization()
            );
        }

        $participantViews = [];

        if ($happening->isInteractiveWebinar()) {
            foreach ($happening->getParticipations() as $happeningParticipation) {
                $user = $happeningParticipation->getUser();

                try {
                    $participantView = $this->getUserParticipantInfosHandler->handle(
                        new GetUserParticipantInfos($happening->getEvent(), $user, $query->getLocale())
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
        }

        return new WebinarView(
            $happening->getId(),
            $query->getUser()->getId(),
            $happening->getTitle($query->getLocale()),
            $token,
            $sessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $isSpeaker,
            $speakers,
            $participantViews,
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            $this->dateTime,
            $timeRemainingInSeconds,
            round($timeRemainingInSeconds * 0.2),
            $timeRemainingBeforeStartInSeconds,
            $happening->getWebinarHeaderImage($query->getLocale()),
            $happening->getLiveUrl(),
            $happening->isWebinarRecorded(),
            $this->getWebinarRecordStatus($happening)
        );
    }

    public function getWebinarRecordStatus(Happening $happening): string
    {
        if (!$happening->isWebinarRecorded()) {
            return false;
        }

        $archives = $this->videoConferenceAdapter->listArchives($happening->getWebinarSessionId());
        $status = array_reduce($archives->getItems(), function ($carry, $item) {
            if ($carry[1] < $item->createdAt) {
                $carry = [$item->status, $item->createdAt];
            }
            return $carry;
        }, [ 'no_record', 0 ]);

        return $status[0];
    }
}
