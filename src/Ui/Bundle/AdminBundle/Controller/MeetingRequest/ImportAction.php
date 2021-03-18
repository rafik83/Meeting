<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\Import;
use Proximum\Vimeet\Application\Exception\Import\InvalidEmailException;
use Proximum\Vimeet\Application\Exception\Import\InvalidKeysException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\MultipleParticipantsFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\ParticipantsOfSameSheetException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\UserNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest\ImportType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ImportAction
{
    public const TEMPLATE = 'AdminBundle:MeetingRequest:import.html.twig';

    private Environment $twig;
    private RouterInterface $router;
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private FormFactoryInterface $formFactory;
    private ErrorFactory $errorFactory;
    private FlashBagInterface $flashBag;
    private CommandBusInterface $commandBus;

    public function __construct(
        Environment $twig,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        ErrorFactory $errorFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus
    ) {
        $this->twig = $twig;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->errorFactory = $errorFactory;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access Denied!');
        }

        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Action not allowed for this user');
        }

        $import = new Import($event);
        $form = $this->formFactory->create(ImportType::class, $import, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($import);
                $this->flashBag->add('success', 'flash.admin.meeting_request.import.success');

                return new RedirectResponse($this->router->generate('admin_meeting_request_list', ['event' => $event->getId()]));
            } catch (InvalidKeysException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create($exception->getMessage(), $request->getLocale())
                );
            } catch (InvalidEmailException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create(
                        'form.meeting_request_import.children.file.error.invalid_email',
                        $request->getLocale(),
                        'forms',
                        ['%email%' => $exception->getInvalidEmail()]
                    )
                );
            } catch (UserNotFoundException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create(
                        'form.meeting_request_import.children.file.error.user_not_found',
                        $request->getLocale(),
                        'forms',
                        ['%email%' => $exception->getEmail()]
                    )
                );
            } catch (ParticipantsOfSameSheetException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create(
                        'form.meeting_request_import.children.file.error.users_of_same_sheet',
                        $request->getLocale(),
                        'forms',
                        ['%email_from%' => $exception->getEmailFrom(), '%email_to%' => $exception->getEmailTo()]
                    )
                );
            } catch (ParticipantNotFoundException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create(
                        'form.meeting_request_import.children.file.error.participant_not_found',
                        $request->getLocale(),
                        'forms',
                        ['%email%' => $exception->getEmail()]
                    )
                );
            } catch (MultipleParticipantsFoundException $exception) {
                $form->get('file')->addError(
                    $this->errorFactory->create(
                        'form.meeting_request_import.children.file.error.multiple_sheets',
                        $request->getLocale(),
                        'forms',
                        ['%email%' => $exception->getEmail()]
                    )
                );
            }

        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
