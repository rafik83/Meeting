<?php


namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;


use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;

class NotificationSubscriptions extends AbstractNotification implements NotificationSubscriptionsInterface
{
    private string $mercureHubUrl;
    private string $mercureSubscriberKey;
    private HttpAdapterInterface $httpAdapter;
    private ?array $loadedResults = null;

    public function __construct(
        string $mercureHubUrl,
        string $mercureSubscriberKey,
        HttpAdapterInterface $httpAdapter
    ) {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
        $this->httpAdapter = $httpAdapter;
    }

    public function getSubscriptions(int $eventId, ?int $currentUserId, string $topicFilter = null): array
    {
        if ($this->loadedResults === null) {
            $this->loadedResults = $this->getSubscriptionsRaw();
        }

        $users = [];
        foreach ($this->loadedResults['subscriptions'] as $subscription) {
            if ($topicFilter && $subscription['topic'] !== $topicFilter) {
                continue;
            }
            if (!isset($subscription['payload']['eventId']) || $subscription['payload']['eventId'] != $eventId) {
                continue;
            }
            if (!isset($subscription['payload']['userId'])) {
                continue;
            }
            // exclude current user
            if ($currentUserId && $currentUserId === $subscription['payload']['userId']) {
                continue;
            }
            $users[$subscription['payload']['userId']] = $subscription['payload'];
        }

        uasort($users, function ($user1, $user2) {
            $compareResult = strcmp($user1['userLastName'], $user2['userLastName']);

            if ($compareResult === 0) {
                $compareResult = strcmp($user1['userFirstName'], $user2['userFirstName']);
            }
            return $compareResult;
        });

        return $users;
    }

    public function getStreamSubscriptionsCount(int $happeningId): int
    {
        $result = $this->getSubscriptionsRaw($this->getHappeningTopic($happeningId, AbstractNotification::TYPE_STREAM));

        $indexedUsers = array_reduce($result['subscriptions'], function ($carry, $item) {
            if ($item['payload']['userId'] ?? false) {
                $carry[$item['payload']['userId']] = 1;
            }
            return $carry;
        }, []);

        return count($indexedUsers);
    }

    public function hasUserSubscription(int $eventId, int $userId): bool
    {
        return !empty($this->getSubscriptions($eventId, null, $this->getUserTopic($eventId, $userId)));
    }

    private function getSubscriptionsRaw(?string $topic = null): array
    {
        $authPayload = [
            'mercure' => [
                'subscribe' => ['*'],
            ]
        ];

        $topicUrlPart = '';
        if (null !== $topic) {
            $topicUrlPart = '/' . urlencode($topic);
        }

        $response = $this->httpAdapter->get($this->mercureHubUrl . '/subscriptions' . $topicUrlPart, [
            'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercureSubscriberKey)),
            'Content-type' => 'application/x-www-form-urlencoded',
        ]);

        return json_decode($response->body, true);
    }
}
