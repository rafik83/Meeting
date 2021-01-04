<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\Participant\EmailChecker;
use Proximum\Vimeet\Application\Command\Event\Participant\EmailCheckerHandler;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQuery;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant\SearchEmailForFastCheckinType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class QRCodeReaderAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        \DateTimeInterface $dateTime,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->dateTime = $dateTime;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$event->isAccessControlEnabled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        /** @var QRCodeIdentifierListView $identifiers */
        $identifiers = $this->queryBus->handle(
            new GetQRCodeIdentifiersByEventQuery(
                $event,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        $emailCheckerQuery = new EmailChecker($event);
        $fastCheckinEmailCheckerForm = $this->formFactory->create(
            SearchEmailForFastCheckinType::class,
            $emailCheckerQuery
        );

        if ($fastCheckinEmailCheckerForm->handleRequest($request)->isSubmitted()
            && $fastCheckinEmailCheckerForm->isValid()) {
            /** @var string $emailCheckStatus */
            $emailCheckStatus = $this->queryBus->handle($emailCheckerQuery);

            switch ($emailCheckStatus) {
                case EmailCheckerHandler::EMAIL_KNOWN_FROM_EVENT:
                    $this->flashBag->add('success', 'flash.admin.fast-checkin.add.already-registered');

                    $user = $this->userRepository->findByEmail($emailCheckerQuery->email);

                    return new RedirectResponse(
                        $this->router->generate(
                            'admin_event_fast_checkin_actions',
                            [
                                'event' => $event->getId(),
                                'user' => $user->getId(),
                            ]
                        )
                    );

                case EmailCheckerHandler::EMAIL_KNOWN_FROM_VIMEET:
                case EmailCheckerHandler::EMAIL_UNKNOWN:
                    return new RedirectResponse(
                        $this->router->generate(
                            'admin_event_fast_checkin_form',
                            ['event' => $event->getId(), 'email' => $emailCheckerQuery->email]
                        )
                    );
            }
        }

        return new Response(
            $this->engine->render(
                '@Admin/Event/qrCodeReader.html.twig',
                [
                    'event' => $event,
                    'identifiers' => $identifiers->list,
                    'date' => $this->dateTime,
                    'fastCheckinEmailCheckerForm' => $fastCheckinEmailCheckerForm->createView(),
                ]
            )
        );
    }
}
