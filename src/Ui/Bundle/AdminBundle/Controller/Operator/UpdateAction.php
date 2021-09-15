<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Operator;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Operator\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\UpdateType;
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

    public function __invoke(
        Request $request,
        AdminDomain $adminDomain,
        Admin $operator
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ORGANIZER')) {
            throw new AccessDeniedException('Access denied for admin not organizer');
        }

        if (!$operator->isOperator()) {
            throw new AccessDeniedException('Only operator can be updated with this page');
        }

        $currentAdmin = $adminDomain->getAdmin();
        $eventsAllowedByAdmin = $currentAdmin->getEvents()->toArray();

        $update = new Update($operator, $eventsAllowedByAdmin);
        $form   = $this->formFactory->create(UpdateType::class, $update, [
            'submit' => true,
            'events' => $eventsAllowedByAdmin,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->flashBag->add('success', 'flash.admin.operator.update.success');

                return new RedirectResponse($this->router->generate('admin_list_operator'));
            } catch (EmailAlreadyExistsException $ex) {
                $error = $this->errorFactory->create('validators.emailAlreadyExist', $request->getLocale());
                $form->get('email')->addError($error);
            }
        }

        return new Response($this->twig->render('AdminBundle:Operator:update.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
