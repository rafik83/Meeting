<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Admin\Delete;
use Proximum\Vimeet\Application\Exception\Admin\AdminLinkedToPlannerJobException;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Security\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DeleteAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        CommandBusInterface $commandBus,
        CsrfTokenManagerInterface $csrfTokenManager,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FlashBagInterface $flashBag
    ) {
        $this->commandBus = $commandBus;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Admin $admin, string $type): RedirectResponse
    {
        $csrfToken = new CsrfToken(IntentionType::INTENTION_REMOVE_ADMIN, $request->get('_token'));
        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedException();
        }

        if (!$this->authorizationChecker->isGranted(AdminVoter::DELETE, $admin)) {
            $this->flashBag->add('error', 'flash.admin.remove.unauthorized');
        } else {
            $this->commandBus->handle(new Delete($admin));
            $this->flashBag->add('success', 'flash.admin.remove.success');
        }

        return new RedirectResponse(
            $this->urlGenerator->generate(
                'admin' === $type ? 'admin_list_admin' : 'admin_list_operator'
            )
        );
    }
}
