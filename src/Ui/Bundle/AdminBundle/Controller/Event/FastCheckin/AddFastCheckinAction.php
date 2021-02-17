<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckin;
use Proximum\Vimeet\Application\Command\Event\Participant\TypeMissingForFastCheckinException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant\AddFastCheckinType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AddFastCheckinAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    private FormFactoryInterface $formFactory;
    private Environment $engine;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        Environment $engine,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        TranslatorInterface $translator
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, string $email)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$event->isAccessControlEnabled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $user = $this->userRepository->findByEmail($email);

        $addFastCheckin = new AddFastCheckin($event, $email, $user);

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
            } catch (TypeMissingForFastCheckinException $exception) {
                $formError = new FormError(
                    $this->translator->trans(
                        'form.add_fast_checkin.children.type.error.missing',
                        [],
                        'forms',
                        $request->getLocale()
                    )
                );
                $form->get('type')->addError($formError);
            } catch (\Exception $exception) {
                $this->flashBag->add('error', 'flash.admin.fast-checkin.add.error');
            }
        }

        return new Response($this->engine->render(
            '@Admin/Event/fastCheckinForm.html.twig',
            [
                'form' => $form->createView(),
            ]
        ));
    }
}
