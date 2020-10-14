<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Controller;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckAction
{
    /** @var ManagerRegistry|null */
    private $doctrine;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ?ManagerRegistry $doctrine,
        LoggerInterface $logger
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    public function __invoke(): Response
    {
        if (!$this->checkDoctrineConnection($this->doctrine->getConnection())) {
            return new Response('KO', 503);
        }

        return new Response('OK', 200);
    }

    private function checkDoctrineConnection(Connection $connection): bool
    {
        try {
            return $connection->ping();
        } catch (\Exception $exception) {
            $this->logger->warning(
                sprintf('VIMEET - The database connection is not ready, message : %s', $exception->getMessage())
            );

            return false;
        }
    }
}
