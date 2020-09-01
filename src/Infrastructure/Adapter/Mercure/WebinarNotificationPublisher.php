<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Mercure;

use Firebase\JWT\JWT;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Adapter\WebinarNotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\Happening;

class WebinarNotificationPublisher extends AbstractWebinarNotification implements WebinarNotificationPublisherInterface
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
    ) {
        $this->mercureHubUrl = $mercureHubUrl;
        $this->mercurePublisherKey = $mercurePublisherKey;
        $this->httpAdapter = $httpAdapter;
    }

    public function send(Happening $happening, string $type, array $data): void
    {
        $authPayload = [
            'mercure' => [
                'publish' => ['*'],
            ]
        ];

        $postData = [
            'topic' => $this->getTopic($happening->getId(), $type),
            'data' => json_encode($data),
        ];

        $this->httpAdapter->post($this->mercureHubUrl, [
                'Authorization' => sprintf('Bearer %s', JWT::encode($authPayload, $this->mercurePublisherKey)),
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($postData)
        );
    }
}
