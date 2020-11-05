<?php


namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;


use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;


class NotificationSubscriptions extends AbstractNotification implements NotificationSubscriptionsInterface
{
    /** @var string */
    private $mercureHubUrl;

    /** @var string */
    private $mercureSubscriberKey;

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    public function __construct(
        string $mercureHubUrl,
        string $mercureSubscriberKey,
        HttpAdapterInterface $httpAdapter
    ) {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
        $this->httpAdapter = $httpAdapter;
    }

    public function getSubscriptions(int $eventId, int $userId): array
    {
        $result = $this->getSubscriptionsRaw();

        $users = [];

        foreach ($result['subscriptions'] as $subscription) {
            if (!isset($subscription['payload']['eventId']) || $subscription['payload']['eventId'] != $eventId) {
                continue;
            }
            if (!isset($subscription['payload']['userId'])) {
                continue;
            }
            // exclude current user
            if ($userId === $subscription['payload']['userId']) {
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
