<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\CallVisio\CallVisioNotAllowedException;
use Proximum\Vimeet\Application\Exception\CallVisio\SessionIdAlreadyCreatedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatSessionNotFoundException;
use Proximum\Vimeet\Application\View\Networking\CallVisioView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;
use Psr\Log\LoggerInterface;

class CallVisioQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var CallVisioPrivateChatAccessChecker */
    private $callVisioPrivateChatAccessChecker;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeImmutable */
    private $now;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        ChatSessionRepositoryInterface $chatSessionRepository,
        CallVisioPrivateChatAccessChecker $callVisioPrivateChatAccessChecker,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VisioSettingsRepositoryInterface $visioSettingsRepository,
        \DateTimeImmutable $now,
        ?LoggerInterface $logger = null
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->callVisioPrivateChatAccessChecker = $callVisioPrivateChatAccessChecker;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->visioSettingsRepository = $visioSettingsRepository;
        $this->now = $now;
        $this->logger = $logger;
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
            throw new CallVisioNotAllowedException();
        }

        $session = null;
        $visioSessionId = $chatSession->getVisioSessionId();
        if (null === $visioSessionId) {
            $session = $this->videoConferenceAdapter->createSession();
            $visioSessionId = $session->getSessionId();
            try {
                $this->chatSessionRepository->addNewSessionId($chatSession, $visioSessionId);
            } catch (SessionIdAlreadyCreatedException $e) {
                // edge case where 2 requests have been processed simultaneously
                $visioSessionId = $e->getExistingSessionId();
                if ($this->logger) {
                    $this->logger->warning(sprintf(
                        '[CallVisioQueryHandler] ChatSession #%d has already a sessionId (likely due to race condition)',
                        $chatSession->getId()
                    ));
                }
            }
        }
        if (null === $session) {
            $session = $this->videoConferenceAdapter->getSession($visioSessionId);
        }

        $dateTimeEnd = clone $this->now->add(new \DateInterval('PT1H'));

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $dateTimeEnd
        );

        $timeRemainingInSeconds = 15 * 60;

        $visioSettings = $this->visioSettingsRepository->getByEvent($visioQuery->sheet->getEvent());

        $callVisioTopic = $this->notificationSubscriber->getCallVisioTopic($visioQuery->sheet->getEvent());

        $providerUrl = $this->notificationSubscriber->getUrl();

        $subscriberKey = $this->notificationSubscriber->getUserSubscriberKey($visioQuery->sheet, $visioQuery->fromUser);

        return new CallVisioView(
            $token,
            $visioSessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $timeRemainingInSeconds,
            round($timeRemainingInSeconds * 0.2),
            $visioSettings !== null ? $visioSettings->getHeader($visioQuery->locale) : null,
            $visioSettings !== null ? $visioSettings->getEndSound($visioQuery->locale) :null,
            $visioSettings !== null ? $visioSettings->getEndImage($visioQuery->locale) : null,
            $visioSettings !== null ? $visioSettings->getEndMessage($visioQuery->locale) : null,
            $callVisioTopic,
            $providerUrl,
            $subscriberKey
        );
    }
}
