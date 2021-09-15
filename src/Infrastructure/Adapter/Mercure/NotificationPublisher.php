<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Chat\NotificationType;
use Proximum\Vimeet\Domain\KeyDates\Checker\AskCallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PublishDelayedPollVoteNotificationMessage;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPoll;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollHandler;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use RuntimeException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\PersistingStoreInterface;

class NotificationPublisher extends AbstractNotification implements NotificationPublisherInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercurePublisherKey;

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var UserPayloadBuilder */
    private $userPayloadBuilder;

    private AskCallVisioPrivateChatAccessChecker $askCallVisioPrivateChatAccessChecker;
    private MessageBusInterface $messageBus;
    private PersistingStoreInterface $jobLockStore;
    private GetHappeningPollHandler $getHappeningPollHandler;

    public function __construct(
        string $mercureHubUrl,
        string $mercurePublisherKey,
        HttpAdapterInterface $httpAdapter,
        UserPayloadBuilder $userPayloadBuilder,
        AskCallVisioPrivateChatAccessChecker $askCallVisioPrivateChatAccessChecker,
        MessageBusInterface $messageBus,
        PersistingStoreInterface $jobLockStore,
        GetHappeningPollHandler $getHappeningPollHandler
    ) {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercurePublisherKey = $mercurePublisherKey;
        $this->httpAdapter = $httpAdapter;
        $this->userPayloadBuilder = $userPayloadBuilder;
        $this->askCallVisioPrivateChatAccessChecker = $askCallVisioPrivateChatAccessChecker;
        $this->messageBus = $messageBus;
        $this->jobLockStore = $jobLockStore;
        $this->getHappeningPollHandler = $getHappeningPollHandler;
    }

    public function publishHappeningNotification(Happening $happening, string $type, array $data): void
    {
        $postData = [
            'topic' => $this->getHappeningTopic($happening->getId(), $type),
            'data' => json_encode($data),
        ];

        $this->publishMessage($postData);
    }

    public function publishChatMessageNotification(
        ChatMessageLinkableInterface $object,
        ChatMessage $message,
        int $messageCount,
        string $action = NotificationType::ADD_CHAT_MESSAGE
    ): void {
        $payload = [
            'action' => $action,
            'msg_count' => $messageCount,
        ];

        if ($object instanceof Happening) {
            $topic = $this->getHappeningTopic($object->getId(), NotificationSubscriber::TYPE_CHAT);
        } elseif ($object instanceof ChatSession) {
            $topic = $this->getUserTopic($object->getEvent()->getId(), $object->getOtherUser($message->getCreatedBy())->getId());
            $payload['content'] = $message->getContent();
            $payload['author'] = $message->getCreatedBy()->getFullname();
            $payload['authorId'] = $message->getCreatedBy()->getId();
            $payload['visioEnable'] = $this->askCallVisioPrivateChatAccessChecker->allowedToAccess($object->getEvent(), $object, $object->getToUser());
        } elseif ($object instanceof Meeting) {
            //todo: add special topic for meeting
            return;
        } elseif ($object instanceof Event) {
            $topic = $this->getNetworkingTopic($object->getEvent()->getId());
        } else {
            throw new RuntimeException('Unsupported chat message type '.$object->getObjectType());
        }

        $postData = [
            'topic' => $topic,
            'data' => json_encode($payload),
        ];

        $this->publishMessage($postData);
    }

    public function publishChatVoteNotification(ChatMessageLinkableInterface $object, ChatMessage $chatMessage, array $votes): void
    {

        if ($object instanceof Happening) {
            $topic = $this->getHappeningTopic($object->getId(), NotificationSubscriber::TYPE_CHAT);
        } elseif ($object instanceof ChatSession) {
            $topic = $this->getUserTopic($object->getEvent()->getId(), $chatMessage->getCreatedBy()->getId());
        } elseif ($object instanceof Meeting) {
            //todo: add special topic for meeting
            return;
        } else {
            $topic = $this->getNetworkingTopic($object->getEvent()->getId());
        }

        $postData = [
            'topic' => $topic,
            'data' => json_encode(['action' => 'update_chat_message_votes', 'messageId' => $chatMessage->getId(), 'votes' => $votes]),
        ];

        $this->publishMessage($postData);
    }

    public function publishUserConnectionNotification(Sheet $sheet, User $user): void
    {
        $postData = [
            'topic' => $this->getNetworkingTopic($sheet->getEvent()->getId()),
            'data' => json_encode(
                array_merge(['action' => 'user_connection'], $this->userPayloadBuilder->get($sheet, $user))
            ),
        ];

        $this->publishMessage($postData);
    }

    public function publishRequestVisioNotification(Sheet $sheet, User $fromUser, int $toUserId, string $type): void
    {
        $postData = [
            'topic' => $this->getUserTopic($sheet->getEvent()->getId(), $toUserId),
            'data' => json_encode([
                'action' => $type,
                'from' => $this->userPayloadBuilder->get($sheet, $fromUser),
                'urlAccept' => '/fr/sheet/'.$sheet->getId().'/networking/visio/'.$fromUser->getId(),
                'urlRefuse' => '/fr/sheet/'.$sheet->getId().'/networking/refuse-visio/'.$fromUser->getId(),
            ]),
        ];

        $this->publishMessage($postData);
    }

    public function publishDelayedPollVoteNotification(Happening\Poll $poll): void
    {
        $key = new Key('poll_vote_notification;' . $poll->getId());

        $lock = new Lock($key, $this->jobLockStore, PublishDelayedPollVoteNotificationMessage::WAIT_DELAY);

        if (!$lock->acquire()) {
            return;
        }

        $message = new PublishDelayedPollVoteNotificationMessage($poll);
        $this->messageBus->dispatchDelayed($message, PublishDelayedPollVoteNotificationMessage::WAIT_DELAY);
    }

    public function publishedPollVoteNotification(Happening\Poll $poll): void
    {
        $topic = $this->getPollResultsTopic($poll);

        $postData = [
            'topic' => $topic,
            'data' => json_encode(
                [
                    'action' => 'poll_vote_added',
                    'poll' => $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, true)),
                ],
                JSON_THROW_ON_ERROR
            ),
        ];

        $this->publishMessage($postData);
    }

    public function publishNewPublishedPollNotification(Happening\Poll $poll): void
    {
        $topic = $this->getHappeningTopic($poll->getHappening()->getId(), NotificationSubscriber::TYPE_POLL);

        $postData = [
            'topic' => $topic,
            'data' => json_encode(
                [
                    'action' => 'new_poll_published',
                    'poll' => $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, false)),
                ],
                JSON_THROW_ON_ERROR
            ),
        ];

        $this->publishMessage($postData);
    }

    public function publishHiddenPollNotification(Happening\Poll $poll): void
    {
        $topic = $this->getHappeningTopic($poll->getHappening()->getId(), NotificationSubscriber::TYPE_POLL);

        $postData = [
            'topic' => $topic,
            'data' => json_encode(
                [
                    'action' => 'poll_hidden',
                    'pollId' => $poll->getId(),
                ],
                JSON_THROW_ON_ERROR
            ),
        ];

        $this->publishMessage($postData);
    }

    private function publishMessage(array $postData)
    {
        $authPayload = [
            'mercure' => [
                'publish' => ['*'],
            ]
        ];

        $this->httpAdapter->post(
            $this->mercureHubUrl,
            [
                'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercurePublisherKey)),
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($postData)
        );
    }
}
