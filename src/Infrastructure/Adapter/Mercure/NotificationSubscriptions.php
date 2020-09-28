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
    )
    {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercureSubscriberKey = $mercureSubscriberKey;
        $this->httpAdapter = $httpAdapter;
    }

    public function getSubscriptions(string $topic): array
    {
        $authPayload = [
            'mercure' => [
                'subscribe' => ['*'],
            ]
        ];

        $response = $this->httpAdapter->get($this->mercureHubUrl . '/subscriptions', [
                'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercureSubscriberKey)),
                'Content-type' => 'application/x-www-form-urlencoded',
            ]
        );
        $result = json_decode($response->body, true);
        $users = [];

        foreach ($result['subscriptions'] as $subscription) {
            if ($subscription['topic'] !== $topic) {
                continue;
            }

            $payload = $subscription['payload'];

            unset($payload['userId']);

            $users[$subscription['payload']['userId']] = $payload;
        }

        return $users;
    }
}
