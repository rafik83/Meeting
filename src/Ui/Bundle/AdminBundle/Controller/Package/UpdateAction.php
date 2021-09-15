<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Package;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Package\Update;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    const TEMPLATE = 'AdminBundle:Package:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var Environment */
    private $twig;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        Environment $twig,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->twig = $twig;
        $this->router = $router;
    }

    /**
     * @param Request     $request
     * @param Package     $package
     * @param AdminDomain $adminDomain
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, Package $package, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $package->getEvent())
        ) {
            throw new AccessDeniedException();
        }

        $update = new Update($package);
        $form   = $this->formFactory->create(UpdateType::class, $update, [
            'event'  => $package->getEvent(),
            'locale' => $package->getEvent()->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.template.package.update.success');

            return new RedirectResponse($this->router->generate('admin_package_update', [
                'package' => $package->getId(),
            ]));
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'form'  => $form->createView(),
            'event' => $package->getEvent(),
        ]));
    }
}
