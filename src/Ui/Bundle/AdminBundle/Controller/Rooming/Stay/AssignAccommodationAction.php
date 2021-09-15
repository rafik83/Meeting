<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Rooming\Accommodation\HasNoRemainingOvernightException;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriodException;
use Proximum\Vimeet\Domain\Rooming\Stay\RoommateHasStayForPeriodException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay\AssignAccommodationType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AssignAccommodationAction
{
    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;

    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        TranslatorInterface $translator,
        SheetRepositoryInterface $sheetRepository,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->translator = $translator;
        $this->sheetRepository = $sheetRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, Event $event, User $user): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $arrival = $request->get('arrivalDate', null);
        $departure = $request->get('departureDate', null);
        $dataForm = $request->request->get('admin_assign_accommodation_type', null);

        $sheet = null;
        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $arrival);
        $departureDate = \DateTime::createFromFormat('d/m/Y', $departure);
        if ($dataForm) {
            $sheetIdString = $dataForm['otherSheet'];
            if ($sheetIdString && is_numeric($sheetIdString)) {
                $sheet = $this->sheetRepository->getSheetById((int) $sheetIdString);
            }
        }

        if (!$arrival || !$departure || !$arrivalDate || !$departureDate) {
            throw new BadRequestHttpException();
        }

        $assignAccommodation = new AssignAccommodation($event, $user, $arrivalDate, $departureDate, $sheet);
        $form = $this->formFactory->create(AssignAccommodationType::class, $assignAccommodation, [
            'submit' => true,
            'assignAccommodation' => $assignAccommodation,
            'attr' => [
                'data-availability-url' => $this->router->generate(
                    'admin_event_rooming_assign_accommodation_stay_availability',
                    [
                        'event' => $event->getId(),
                        'user' => $user->getId(),
                    ]
                ),
                'data-sheets-url' => $this->router->generate(
                    'admin_event_rooming_assign_accommodation_stay_sheets',
                    [
                        'event' => $event->getId(),
                    ]
                ),
                'data-roommate-placeholder' => 'Aucune',
            ]
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($assignAccommodation);

                return new RedirectResponse(
                    $this->router->generate('admin_event_rooming_list', [
                        'event' => $event->getId(),
                        '_fragment' => sprintf('user_%s', $user->getId()),
                    ])
                );
            } catch (HasNoRemainingOvernightException $exception) {
                $form->get('accommodation')->addError(
                    new FormError($this->translator->trans('validators.rooming.accommodation.hasNoRemainingOvernightException', [], 'validators'))
                );
            } catch (HasStayForPeriodException $exception) {
                $form->get('arrival')->addError(
                    new FormError($this->translator->trans('validators.rooming.accommodation.hasStayForPeriodException', [], 'validators'))
                );
            } catch (RoommateHasStayForPeriodException $exception) {
                $form->get('roommate')->addError(
                    new FormError($this->translator->trans('validators.rooming.accommodation.roommateHasStayForPeriodException', [], 'validators'))
                );
            }
        }

        return new Response($this->twig->render('@Admin/Rooming/Stay/assignAccommodation.html.twig', [
            'userName' => $user->getFullname(),
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
