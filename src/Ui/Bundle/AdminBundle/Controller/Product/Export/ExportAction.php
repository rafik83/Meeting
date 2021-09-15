<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Export;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Product\Export\ExportProductsJobCreator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;
    
    /** @var CommandBusInterface */
    private $commandBus;
    
    /** @var RouterInterface */
    private $router;
    
    /** @var FlashBagInterface */
    private $flashBag;
    
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }
    
    /**
     * @param Request       $request
     * @param UserInterface $admin
     * @param Event         $event
     * @param AdminDomain   $adminDomain
     *
     * @return RedirectResponse
     */
    public function __invoke(Request $request, UserInterface $admin, Event $event, AdminDomain $adminDomain): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }
        
        $exportJobCreator = new ExportProductsJobCreator($event, $adminDomain->getAdmin(), $request->getLocale());
        $this->commandBus->handle($exportJobCreator);
    
        $this->flashBag->add('success', 'flash.admin.product.export.success');
        
        return new RedirectResponse($this->router->generate('admin_product', [
            'event' => $event->getId()
        ]));
    }
}
