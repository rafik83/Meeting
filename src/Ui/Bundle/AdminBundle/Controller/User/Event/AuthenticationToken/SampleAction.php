<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\AuthenticationToken;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\User\Event\Denormalizer\AuthenticationTokenDenormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SampleAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function __invoke(Request $request, Event $event): CsvFileResponse
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN') ||
            false === $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $sample = implode(AuthenticationTokenDenormalizer::ALLOWED_KEYS, ';') . '
aa@aa.fr;AABBCCDDEE;2020-01-01
bb@bb.fr;FFGGHHIIKK;';

        return new CsvFileResponse($sample, 'authentication-token-import-sample.csv');
    }
}
