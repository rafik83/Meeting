<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\Event\PreviewTipViewQuery;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PreviewAction
{
    /** @var CommandBus */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /**
     * @param CommandBus                           $commandBus
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        CommandBus $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param Tip    $tip
     * @param string $locale
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function __invoke(Tip $tip, string $locale): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || $tip->hasEvent()
        ) {
            throw new AccessDeniedException('The tip has an event and can not be previewed');
        }

        $tipView = $this->commandBus->handle(new PreviewTipViewQuery($tip, $locale));

        return new JsonResponse($tipView);
    }
}
