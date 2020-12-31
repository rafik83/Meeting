<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\Availability\Confirmation;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Availability\ConfirmationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\AvailabilityConfirmationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class AvailabilityController extends Controller
{
    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     */
    public function confirmationAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserInterface $user
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (Type::TYPE_MANAGEMENT_UNAVAILABLE !== $sheet->getType()->getAvailabilityType()) {
            throw new AccessDeniedException();
        }

        // The Agenda needs to be open to access this page as it allows the addition of unavailabilities
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());

        $availabilityConfirmation = new Confirmation($eventDomain->getEvent(), $user);
        $form = $this->createForm(ConfirmationType::class, $availabilityConfirmation);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($availabilityConfirmation);

            if ($this->hasFlash(AvailabilityConfirmationChecker::ORIGIN_MEETING_REQUEST_MANAGEMENT)) {
                return $this->redirectToRoute('event_meeting_list_request', [
                    'sheet' => $sheet->getId(),
                ]);
            }

            return $this->redirectToRoute('event_catalog_index', [
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->render('EventBundle:Availability:confirmation.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
            'sheet' => $sheet,
        ]);
    }

    /**
     * @param string $flash
     *
     * @return bool
     */
    private function hasFlash(string $flash): bool
    {
        $values = $this->container->get('session')->getFlashBag()->get($flash);

        return !empty($values);
    }
}
