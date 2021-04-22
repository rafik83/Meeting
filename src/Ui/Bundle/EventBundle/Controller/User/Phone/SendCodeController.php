<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\ValidateMobileAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SendCodeController extends AbstractController
{
    private SendCodeFormHandler $sendCodeFormHandler;

    public function __construct(
        SendCodeFormHandler $sendCodeFormHandler
    ) {
        $this->sendCodeFormHandler = $sendCodeFormHandler;
    }

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

        $sendCodeView = $this->sendCodeFormHandler->handle(
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
