<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveAction
{
    /** @var CommandBus */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /**
     * @param CommandBus                           $commandBus
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        CommandBus $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param Tip $tip
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function __invoke(Tip $tip): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            || $tip->hasEvent()) {
            throw new AccessDeniedException();
        }

        $this->commandBus->handle(new Remove($tip));
        $this->flashBag->add('success', 'flash.admin.tip.remove.success');

        return new RedirectResponse($this->router->generate('admin_tip_list'));
    }
}
