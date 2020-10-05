<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class NotificationPublisher extends AbstractNotification implements NotificationPublisherInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercurePublisherKey;

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    public function __construct(
        string $mercureHubUrl,
        string $mercurePublisherKey,
        HttpAdapterInterface $httpAdapter
    )
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercurePublisherKey = $mercurePublisherKey;
        $this->httpAdapter = $httpAdapter;
    }

    public function publishHappeningNotification(Happening $happening, string $type, array $data): void
    {
        $postData = [
            'topic' => $this->getHappeningTopic($happening->getId(), $type),
            'data' => json_encode($data),
        ];

        $this->publishMessage($postData);
    }

    public function publishChatMessageNotification(ChatMessageLinkableInterface $object): void
    {
        if ($object instanceof Happening) {
            $topic = $this->getHappeningTopic($object->getId(), NotificationSubscriber::TYPE_CHAT);
        } elseif ($object instanceof ChatSession) {
            $topic = $this->getChatSessionTopic($object);
        } else {
            $topic = $this->getNotificationTopic($object->getEvent()->getId());
        }

        $postData = [
            'topic' => $topic,
            'data' => json_encode(['action' => 'add_chat_message']),
        ];

        $this->publishMessage($postData);
    }

    public function publishChatVoteNotification(ChatMessageLinkableInterface $object, int $chatMessageId, array $votes): void
    {

        if ($object instanceof Happening) {
            $topic = $this->getHappeningTopic($object->getId(), NotificationSubscriber::TYPE_CHAT);
        } elseif ($object instanceof ChatSession) {
            $topic = $this->getChatSessionTopic($object);
        } else {
            $topic = $this->getNotificationTopic($object->getEvent()->getId());
        }

        $postData = [
            'topic' => $topic,
            'data' => json_encode(['action' => 'update_chat_message_votes', 'messageId' => $chatMessageId, 'votes' => $votes]),
        ];

        $this->publishMessage($postData);
    }

    public function publishUserConnectionNotification(Event $event, User $user): void
    {
        $postData = [
            'topic' => $this->getNotificationTopic($event->getId()),
            'data' => json_encode(['action' => 'user_connection', 'userName' => 'ricardo']),
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

        $this->httpAdapter->post($this->mercureHubUrl, [
            'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercurePublisherKey)),
            'Content-type' => 'application/x-www-form-urlencoded',
        ],
            http_build_query($postData)
        );
    }
}
