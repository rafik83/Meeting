<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckAction
{
    /** @var ManagerRegistry|null */
    private $doctrine;

    public function __construct(
        ?ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
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
        } catch (\Exception $e) {
            return false;
        }
    }
}
