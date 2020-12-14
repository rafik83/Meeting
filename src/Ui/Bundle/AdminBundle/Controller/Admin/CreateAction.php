<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Application\Exception\Admin\EmailAlreadyExistsException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class CreateAction
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

    /** @var EngineInterface */
    private $engine;

    /** @var ErrorFactory */
    private $errorFactory;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine,
        ErrorFactory $errorFactory
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
        $this->errorFactory = $errorFactory;
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied for admin not super admin');
        }

        $create = new Create($request->getLocale());

        $form = $this->formFactory->create(CreateType::class, $create, [
            'submit' => true,
        ]);

        $existingDeleteAdmin = null;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);
                $this->flashBag->add('success', 'flash.admin.admin.create.success');

                return new RedirectResponse($this->router->generate('admin_list_admin'));
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->errorFactory->create('validators.emailAlreadyExist', $request->getLocale())
                );

                if (null !== $ex->getExistingAdmin()->getDeletedAt()){
                    $existingDeleteAdmin = $ex->getExistingAdmin();
                }
            }
        }

        return new Response($this->engine->render('AdminBundle:Admin:create.html.twig', [
            'form' => $form->createView(),
            'existingAdmin' => $existingDeleteAdmin,
        ]));
    }
}
