<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\UpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var Environment */
    private $twig;

    /** @var ErrorFactory */
    private $errorFactory;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        Environment $twig,
        ErrorFactory $errorFactory
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->twig = $twig;
        $this->errorFactory = $errorFactory;
    }

    public function __invoke(Request $request, Admin $admin): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied for admin not super admin');
        }

        $update = new Update($admin);

        $form = $this->formFactory->create(UpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->flashBag->add('success', 'flash.admin.admin.update.success');

                return new RedirectResponse($this->router->generate('admin_list_admin'));
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->errorFactory->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return new Response($this->twig->render('AdminBundle:Admin:update.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
