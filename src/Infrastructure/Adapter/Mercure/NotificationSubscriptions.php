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

    public function getSubscriptions() : array
    {
        $authPayload = [
            'mercure' => [
                'subscribe' => ['*'],
            ]
        ];

        $response = $this->httpAdapter->get($this->mercureHubUrl.'/subscriptions', [
            'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercureSubscriberKey)),
            'Content-type' => 'application/x-www-form-urlencoded',
        ]
        );
        return json_decode($response->body, true);
    }
}
