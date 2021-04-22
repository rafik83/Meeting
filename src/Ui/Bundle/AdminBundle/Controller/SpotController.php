<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Command\Spot\Action\UnVisio;
use Proximum\Vimeet\Application\Command\Spot\Action\Visio;
use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DisableBatch;
use Proximum\Vimeet\Application\Command\Spot\EnableBatch;
use Proximum\Vimeet\Application\Command\Spot\UnavailabilityBatch;
use Proximum\Vimeet\Application\Command\Spot\UnavailabilityBatchResult;
use Proximum\Vimeet\Application\Command\Spot\Update;
use Proximum\Vimeet\Application\Exception\Spot\MultipleUniqueReferenceViolationException;
use Proximum\Vimeet\Application\Exception\Spot\SpotException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Application\Query\Spot\ListViewQuery;
use Proximum\Vimeet\Application\Query\Spot\SpotUnavailabilityQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query\HasEventReferenceQuery;
use Proximum\Vimeet\Application\View\Spot\Batch\DeleteBatchView;
use Proximum\Vimeet\Application\View\Spot\SpotUnavailabilityView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\FilterSummary;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\BatchCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\BatchUnavailabilityType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\FilterSpotType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\SpotCreateType;
use Proximum\Vimeet\Ui\Flash\TransMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpotController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private FilterSummary $filterSummary;
    private SessionInterface $session;
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        FormFactoryInterface $formFactory,
        FilterSummary $filterSummary,
        SessionInterface $session,
        TranslatorInterface $translator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->formFactory = $formFactory;
        $this->filterSummary = $filterSummary;
        $this->session = $session;
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    private function createFilterForm(string $type, array $data, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('', $type, $data, $options);
    }

    public function listAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale     = $event->getAvailableLocale($request->getLocale());
        $filters    = [];
        $filterForm = $this->createFilterForm(FilterSpotType::class, $filters);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
        }

        $spotsList = $this->queryBus->handle(new ListViewQuery($event, $locale, $filters));

        $canExport = $this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            && $this->queryBus->handle(new HasEventReferenceQuery($event));

        $filterFormView = $filterForm->createView();

        return $this->render('AdminBundle:Spot:list.html.twig', [
            'spotsList'       => $spotsList,
            'canExport'       => $canExport,
            'event'           => $event,
            'filter_form'     => $filterForm->createView(),
            'filters_summary' => $this->filterSummary->getFilters($filterFormView, $filters, $event, $locale),
            'filtered'        => $filterForm->isSubmitted() && $filterForm->isValid(),
            'update_url'      => $this->generateUrl('admin_spot_update', ['event' => $event->getId()]),
        ]);
    }

    public function batchAction(Request $request, Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $selectedSpots            = $request->request->get('ids', []);
        $deleteButton             = $request->request->getBoolean('delete');
        $disableButton            = $request->request->getBoolean('disable');
        $enableButton             = $request->request->getBoolean('enable');
        $spotUnavailabilityButton = $request->request->getBoolean('spotUnavailability');

        if (!empty($selectedSpots)) {
            if ($deleteButton) {
                $deleteBatch = new DeleteBatch($selectedSpots, $event);

                /** @var DeleteBatchView $deleteBatchView */
                $deleteBatchView = $this->commandBus->handle($deleteBatch);

                if (!empty($deleteBatchView->spotsWithMeetings)) {
                    $this->addFlash('error', new TransMessage(
                        'flash.admin.spot_batch.delete.failure.meetings',
                        ['%spots%' => $deleteBatchView->getSpotsWithMeetings()]
                    ));
                }

                if (!empty($deleteBatchView->spotsWithSheets)) {
                    $this->addFlash('error', new TransMessage(
                        'flash.admin.spot_batch.delete.failure.sheets',
                        ['%spots%' => $deleteBatchView->getSpotsWithSheets()]
                    ));
                }

                if (!empty($deleteBatchView->deletedSpots)) {
                    $this->addFlash('success', new TransMessage(
                        'flash.admin.spot_batch.delete.success',
                        ['%spots%' => $deleteBatchView->getDeletedSpots()]
                    ));
                }
            } elseif ($disableButton) {
                $disableBatch = new DisableBatch($selectedSpots, $event);
                $this->commandBus->handle($disableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.disable.success');
            } elseif ($enableButton) {
                $enableBatch = new EnableBatch($selectedSpots, $event);
                $this->commandBus->handle($enableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.enable.success');
            } elseif ($spotUnavailabilityButton) {
                $this->session->set('selectedSpots', $selectedSpots);

                return $this->redirectToRoute('admin_spot_batch_unavailability', ['event' => $event->getId()]);
            }
        }

        return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
    }

    public function createAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event);
        $form   = $this->createForm(SpotCreateType::class, $create, [
            'action' => $this->generateUrl('admin_spot_create', ['event' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);
                $this->addFlash('success', 'flash.admin.spot.create.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
            } catch (UniqueReferenceViolationException $exception) {
                $form->get('reference')->addError(
                    new FormError($this->translator->trans('validators.spot.reference.unique', [], 'validators'))
                );
            }
        }

        return $this->render('AdminBundle:Spot:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function batchCreateAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $batchCreate = new BatchCreate($event);
        $form        = $this->createForm(BatchCreateType::class, $batchCreate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($batchCreate);
                $this->addFlash('success', 'flash.admin.spot.batch_create.success');
            } catch (MultipleUniqueReferenceViolationException $exception) {
                $this->addFlash('warning', new TransMessage(
                    'flash.admin.spot.batch_create.duplicate',
                    ['%references%' => $exception->getReferencesAsString()]
                ));
            }

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Spot:batchCreate.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function batchUnavailabilityAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $selectedSpots = $this->session->get('selectedSpots');

        /** @var SpotUnavailabilityView $spotUnavailabilityView */
        $spotUnavailabilityView = $this->queryBus->handle(new SpotUnavailabilityQuery($event, $selectedSpots)
        );

        $meetingSlots = [];

        if (!$spotUnavailabilityView->isSameUnavailabilities()) {
            $meetingSlots = $spotUnavailabilityView->getMeetingSlots();
        } else {
            $this->addFlash('warning', 'flash.admin.spot_batch.spotUnavailability.exist.warning');
        }

        $unavailabilityBatch = new UnavailabilityBatch($selectedSpots, $event, $meetingSlots);

        $form = $this->createForm(BatchUnavailabilityType::class, $unavailabilityBatch, [
            'event'  => $event,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var UnavailabilityBatchResult $result */
            $result = $this->commandBus->handle($unavailabilityBatch);
            $this->addFlash('success', 'flash.admin.spot_batch.spotUnavailability.success');

            if ($result->hasSpotWithMeetingWarning()) {
                $this->addFlash('warning', new TransMessage(
                    'flash.admin.spot_batch.spotUnavailability.spotWithMeetingWarning',
                    ['%spots%' => $result->getSpotReferences()]
                ));
            }

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Spot:batchUnavailability.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function updateAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $data = json_decode($request->getContent(), true);

        try {
            $command = new Update($event, $data['id'], $data['property'], $data['value']);
            $this->commandBus->handle($command);
        } catch (SpotNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (UniqueReferenceViolationException $exception) {
            $error = $this->translator->trans('validators.spot.reference.unique', [], 'validators');

            return new JsonResponse(['error' => $error], 403);
        } catch (SpotException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 403);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }

        return new JsonResponse([
            'id'       => $command->id,
            'property' => $command->property,
            'value'    => $command->value,
        ]);
    }

    public function visioAction(Event $event, Spot $spot): RedirectResponse
    {
        return $this->handleAndRedirect($event, $spot, new Visio($spot));
    }

    public function unVisioAction(Event $event, Spot $spot): RedirectResponse
    {
        return $this->handleAndRedirect($event, $spot, new UnVisio($spot));
    }

    private function handleAndRedirect(Event $event, Spot $spot, Command $command): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfWrongEvent($event, $spot);

        $this->commandBus->handle($command);

        return $this->redirectToRoute('admin_spot_list', ['event' => $spot->getEvent()->getId()]);
    }

    private function denyAccessIfWrongEvent(Event $event, Spot $spot)
    {
        if ($spot->getEvent() !== $event) {
            throw $this->createAccessDeniedException('This spot is not available for this event.');
        }
    }
}
