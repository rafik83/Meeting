<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Partner;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Partner\Create;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

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

    /** @var Environment */
    private $twig;

    /** @var ErrorFactory */
    private $errorFactory;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventRepositoryInterface $eventRepository,
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
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Access denied for admin not organizer');
        }
        $currentAdmin = $adminDomain->getAdmin();

        $create = new Create($currentAdmin);
        $events = $this->eventRepository->getEventsByAdmin($currentAdmin);

        $form = $this->formFactory->create(CreateType::class, $create, [
            'submit' => true,
            'events' => $events,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);
                $this->flashBag->add('success', 'flash.admin.partner.create.success');

                if ($currentAdmin->isSuperAdmin()) {
                    return new RedirectResponse($this->router->generate('admin_list_admin'));
                }

                return new RedirectResponse($this->router->generate('admin_list_operator'));
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->errorFactory->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return new Response($this->twig->render('AdminBundle:Partner:create.html.twig', [
            'form' => $form->createView(),
            'isSuperAdmin' => $currentAdmin->isSuperAdmin(),
        ]));
    }
}
