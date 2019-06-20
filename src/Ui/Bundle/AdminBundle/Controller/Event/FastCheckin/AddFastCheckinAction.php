<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant\AddFastCheckinType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AddFastCheckinAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        CommandBusInterface $commandBus,
        FormFactory $formFactory,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, string $email)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $user = $this->userRepository->findByEmail($email);

        $addFastCheckin = new AddFastCheckin($event, $email);

        if ($user instanceof User) {
            $addFastCheckin->firstname = $user->getFirstName();
            $addFastCheckin->lastname = $user->getLastName();
            $addFastCheckin->mobile = $user->getMobile();
            $addFastCheckin->country = $user->getCountry();
        }

        $form = $this->formFactory->create(
            AddFastCheckinType::class,
            $addFastCheckin,
            [
                'user' => $adminDomain->getAdmin(),
                'locale' => $request->getLocale(),
                'event' => $event,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                /** @var Participant $participant */
                $participant = $this->commandBus->handle($addFastCheckin);

                $this->flashBag->add('success', 'flash.admin.fast-checkin.add.success');

                return new RedirectResponse(
                    $this->router->generate(
                        'admin_event_fast_checkin_actions',
                        [
                            'event' => $event->getId(),
                            'user' => $participant->getUser()->getId(),
                        ]
                    )
                );
            } catch (\Exception $exception) {
                $this->flashBag->add('error', 'flash.admin.fast-checkin.add.error');
            }
        }

        return $this->engine->renderResponse(
            '@Admin/Event/fastCheckinForm.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
