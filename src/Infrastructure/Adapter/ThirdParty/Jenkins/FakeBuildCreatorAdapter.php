<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Psr\Log\LoggerInterface;

class FakeBuildCreatorAdapter implements BuildCreatorInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $buildName, array $arguments = []): string
    {
        $this->logger->info('Build created', ['buildName' => $buildName, 'arguments' => $arguments]);

        return 'Build created';
    }
}
