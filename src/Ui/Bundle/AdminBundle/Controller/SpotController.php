<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

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
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\BatchCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\BatchUnavailabilityType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\FilterSpotType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\SpotCreateType;
use Proximum\Vimeet\Ui\Flash\TransMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpotController extends Controller
{
    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, $options);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale     = $event->getAvailableLocale($request->getLocale());
        $filters    = [];
        $filterForm = $this->createFilterForm(FilterSpotType::class, $filters);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
        }

        $spotsList = $this->get('tactician.commandbus.query')->handle(new ListViewQuery($event, $locale, $filters));

        $canExport = $this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            && $this->get('tactician.commandbus.query')->handle(new HasEventReferenceQuery($event));

        $filterFormView = $filterForm->createView();

        return $this->render('AdminBundle:Spot:list.html.twig', [
            'spotsList'       => $spotsList,
            'canExport'       => $canExport,
            'event'           => $event,
            'filter_form'     => $filterForm->createView(),
            'filters_summary' => $this->get('filter_summary')->getFilters($filterFormView, $filters, $event, $locale),
            'filtered'        => $filterForm->isSubmitted() && $filterForm->isValid(),
            'update_url'      => $this->generateUrl('admin_spot_update', ['event' => $event->getId()]),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse
     */
    public function batchAction(Request $request, Event $event)
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
                $deleteBatchView = $this->get('tactician.commandbus')->handle($deleteBatch);

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
                $this->get('tactician.commandbus')->handle($disableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.disable.success');
            } elseif ($enableButton) {
                $enableBatch = new EnableBatch($selectedSpots, $event);
                $this->get('tactician.commandbus')->handle($enableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.enable.success');
            } elseif ($spotUnavailabilityButton) {
                $this->get('session')->set('selectedSpots', $selectedSpots);

                return $this->redirectToRoute('admin_spot_batch_unavailability', ['event' => $event->getId()]);
            }
        }

        return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
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
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.spot.create.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
            } catch (UniqueReferenceViolationException $exception) {
                $form->get('reference')->addError(new FormError($this->get('translator')
                    ->trans('validators.spot.reference.unique', [], 'validators')));
            }
        }

        return $this->render('AdminBundle:Spot:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function batchCreateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $batchCreate = new BatchCreate($event);
        $form        = $this->createForm(BatchCreateType::class, $batchCreate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($batchCreate);
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

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function batchUnavailabilityAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $selectedSpots = $this->get('session')->get('selectedSpots');

        /** @var SpotUnavailabilityView $spotUnavailabilityView */
        $spotUnavailabilityView = $this->get('tactician.commandbus.query')
            ->handle(new SpotUnavailabilityQuery($event, $selectedSpots)
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

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            /** @var UnavailabilityBatchResult $result */
            $result = $this->get('tactician.commandbus')->handle($unavailabilityBatch);
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

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $data = json_decode($request->getContent(), true);

        try {
            $command = new Update($event, $data['id'], $data['property'], $data['value']);
            $this->get('tactician.commandbus')->handle($command);
        } catch (SpotNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (UniqueReferenceViolationException $exception) {
            $error = $this->get('translator')->trans('validators.spot.reference.unique', [], 'validators');

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

    /**
     * @param Event $event
     * @param Spot  $spot
     *
     * @return RedirectResponse
     */
    public function visioAction(Event $event, Spot $spot)
    {
        return $this->handleAndRedirect($event, $spot, new Visio($spot));
    }

    /**
     * @param Event $event
     * @param Spot  $spot
     *
     * @return RedirectResponse
     */
    public function unVisioAction(Event $event, Spot $spot)
    {
        return $this->handleAndRedirect($event, $spot, new UnVisio($spot));
    }

    /**
     * @param Event $event
     * @param Spot  $spot
     * @param mixed $command
     *
     * @return RedirectResponse
     */
    private function handleAndRedirect(Event $event, Spot $spot, $command)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfWrongEvent($event, $spot);

        $this->get('tactician.commandbus')->handle($command);

        return $this->redirectToRoute('admin_spot_list', ['event' => $spot->getEvent()->getId()]);
    }

    /**
     * @param Event $event
     * @param Spot  $spot
     */
    private function denyAccessIfWrongEvent(Event $event, Spot $spot)
    {
        if ($spot->getEvent() !== $event) {
            throw $this->createAccessDeniedException('This spot is not available for this event.');
        }
    }
}
