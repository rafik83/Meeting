<?php


namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;


use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Domain\Model\User;


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

    public function getSubscriptions(string $topic, User $user): array
    {
        $authPayload = [
            'mercure' => [
                'subscribe' => ['*'],
            ]
        ];

        $response = $this->httpAdapter->get($this->mercureHubUrl . '/subscriptions', [
            'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercureSubscriberKey)),
            'Content-type' => 'application/x-www-form-urlencoded',
        ]);
        $result = json_decode($response->body, true);
        $users = [];

        foreach ($result['subscriptions'] as $subscription) {
            if ($subscription['topic'] !== $topic) {
                continue;
            }
            if (!isset($subscription['payload']['userId'])) {
                continue;
            }
            // exclude current user
            if ($user->getId() === $subscription['payload']['userId']) {
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
}
