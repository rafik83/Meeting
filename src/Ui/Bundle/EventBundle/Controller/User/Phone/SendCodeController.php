<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\ValidateMobileAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SendCodeController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function sendCodeAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(ValidateMobileAccessVoter::PERMISSION_NAME, $eventDomain->getEvent());

        $user         = $this->getUser();
        $mobileNumber = $request->query->get('mobile', $user->getMobile());

        if ($redirectTo = $request->query->get('redirectTo')) {
            $this->addFlash('redirectTo', $redirectTo);
        }

        $sendCodeView = $this->get('handler.user.phone.send_code_form_handler')->handle(
            new SendCodeForm($request, $user, $eventDomain->getEvent(), null, $mobileNumber, true)
        );

        if ($sendCodeView->isSuccess()) {
            return $this->redirectToRoute('event_user_event_phone_validate_code', [
                'sheet'       => $sheet->getId(),
                'participant' => $participant->getId(),
            ]);
        }

        return $this->render('EventBundle:SendCode:validateMobile.html.twig', [
            'event'               => $eventDomain->getEvent(),
            'sendCodeForm'        => null !== $sendCodeView->form ? $sendCodeView->form->createView() : null,
            'tipTranslationViews' => $sendCodeView->tipTranslationViews,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function redirectToSendCodeWithFlashAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(ValidateMobileAccessVoter::PERMISSION_NAME, $eventDomain->getEvent());

        $this->addFlash('success', 'flash.event.user_event_phone.confirmationNeeded');

        if ($redirectTo = $request->query->get('redirectTo')) {
            $this->addFlash('redirectTo', $redirectTo);
        }

        return $this->redirectToRoute('event_user_phone_validate', [
            'sheet'       => $sheet->getId(),
            'participant' => $participant->getId(),
        ]);
    }
}
