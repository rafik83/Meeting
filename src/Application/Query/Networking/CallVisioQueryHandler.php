<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\Chat\ChatSessionNotFoundException;
use Proximum\Vimeet\Application\View\Networking\CallVisioView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class CallVisioQueryHandler
{
    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var CallVisioPrivateChatAccessChecker */
    private $callVisioPrivateChatAccessChecker;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeImmutable */
    private $now;

    /** @var VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        ChatSessionRepositoryInterface $chatSessionRepository,
        CallVisioPrivateChatAccessChecker $callVisioPrivateChatAccessChecker,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VisioSettingsRepositoryInterface $visioSettingsRepository,
        \DateTimeImmutable $now

    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->callVisioPrivateChatAccessChecker = $callVisioPrivateChatAccessChecker;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->visioSettingsRepository = $visioSettingsRepository;
        $this->now = $now;
    }

    public function handle(CallVisioQuery $visioQuery): CallVisioView
    {
        $chatSession = $this->chatSessionRepository->findOneByEventAndUsers(
            $visioQuery->sheet->getEvent(),
            $visioQuery->fromUser,
            $visioQuery->toUser
        );

        if (null === $chatSession) {
            throw new ChatSessionNotFoundException();
        }

        if (!$this->callVisioPrivateChatAccessChecker->allowedToAccess($visioQuery->sheet->getEvent(), $chatSession)) {
            // TODO a faire au propre
            throw new \Exception();
        }

        if ($chatSession->getVisioSessionId() === null) {
            $session = $this->videoConferenceAdapter->createSession();
            $chatSession->setVisioSessionId($session->getSessionId());
            $this->chatSessionRepository->update();
        } else {
            $session = $this->videoConferenceAdapter->getSession($chatSession->getVisioSessionId());
        }

        $dateTimeEnd = $this->now->add(new \DateInterval('PT1H'));

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $dateTimeEnd
        );

        $timeRemainingInSeconds = 15 * 60;

        $visioSettings = $this->visioSettingsRepository->getByEvent($visioQuery->sheet->getEvent());

        return new CallVisioView(
            $token, $chatSession->getVisioSessionId(),
            $this->videoConferenceAdapter->getApiKey(),
            $timeRemainingInSeconds,
            round($timeRemainingInSeconds * 0.2),
            $visioSettings !== null ? $visioSettings->getHeader($visioQuery->locale) : null,
            $visioSettings !== null ? $visioSettings->getEndSound($visioQuery->locale) :null,
            $visioSettings !== null ? $visioSettings->getEndImage($visioQuery->locale) : null,
            $visioSettings !== null ? $visioSettings->getEndMessage($visioQuery->locale) : null
        );
    }
}
