<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Partner;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Partner\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

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

    /** @var EngineInterface */
    private $engine;

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
        EngineInterface $engine,
        ErrorFactory $errorFactory
    ) {
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
        $this->errorFactory = $errorFactory;
        $this->eventRepository = $eventRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Admin $partner): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Access denied for admin not organizer');
        }
        $currentAdmin = $adminDomain->getAdmin();

        if (!$partner->isPartner()) {
            throw new AccessDeniedException('Only partner can be updated with this page');
        }

        $events = $this->eventRepository->getEventsByAdmin($currentAdmin);

        $update = new Update($partner);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'submit' => true,
            'events' => $events,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->flashBag->add('success', 'flash.admin.partner.update.success');

                if ($this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
                    return new RedirectResponse($this->router->generate('admin_list_admin'));
                }

                return new RedirectResponse($this->router->generate('admin_list_operator'));
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->errorFactory->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return new Response($this->engine->render('AdminBundle:Partner:update.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
