<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use RuntimeException;

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

    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(
        string $mercureHubUrl,
        string $mercurePublisherKey,
        HttpAdapterInterface $httpAdapter,
        UserPayloadBuilder $userPayloadBuilder,
        ChatMessageRepositoryInterface $chatMessageRepository,
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercurePublisherKey = $mercurePublisherKey;
        $this->httpAdapter = $httpAdapter;
        $this->userPayloadBuilder = $userPayloadBuilder;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->questionRepository = $questionRepository;
    }

    public function publishHappeningNotification(Happening $happening, string $type, array $data): void
    {
        $data['msg_count'] = $this->questionRepository->getMessagesCountDuringHappening($happening);
        $postData = [
            'topic' => $this->getHappeningTopic($happening->getId(), $type),
            'data' => json_encode($data),
        ];

        $this->publishMessage($postData);
    }

    public function publishChatMessageNotification(ChatMessageLinkableInterface $object, ChatMessage $message): void
    {
        $payload = [
            'action' => 'add_chat_message',
            'msg_count' => $this->chatMessageRepository->getMessagesCountByEvent($object, null),
        ];

        if ($object instanceof Happening) {
            $topic = $this->getHappeningTopic($object->getId(), NotificationSubscriber::TYPE_CHAT);
        } elseif ($object instanceof ChatSession) {
            $topic = $this->getUserTopic($object->getOtherUser($message->getCreatedBy())->getId());
            $payload['content'] = $message->getContent();
            $payload['author'] = $message->getCreatedBy()->getFullname();
            $payload['authorId'] = $message->getCreatedBy()->getId();
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
            $topic = $this->getUserTopic($chatMessage->getCreatedBy()->getId());
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
                array_merge(['action' => 'user_connection',], $this->userPayloadBuilder->get($sheet, $user))
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
