<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Application\Command\User\Phone\ValidateCode;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeAlreadyValidatedException;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeNotValidException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\ValidateMobileAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone\ValidateCodeType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ValidateController extends Controller
{
    /**
     * @param Request            $request
     * @param EventDomain        $eventDomain
     * @param UserEventToken     $userEventToken
     * @param UserInterface|null $user
     *
     * @return Response|RedirectResponse
     */
    public function validateWithTokenAction(
        Request $request,
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ) {
        $event = $eventDomain->getEvent();
        $this->checkUserEventTokenAccess($event, $userEventToken, $user);

        $userEventPhone = $this
            ->get('repository.user.user_event_phone_repository')
            ->find($userEventToken->getUser(), $userEventToken->getEvent())
        ;

        if (null === $userEventPhone || $userEventPhone->isValidated()) {
            throw $this->createNotFoundException('The user event phone is already validated');
        }

        $validate = new ValidateCode($userEventPhone);
        $form     = $this->createForm(ValidateCodeType::class, $validate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($validate);

                return $this->redirectToRoute('event_user_event_phone_validate_code_success', [
                    'token' => $userEventToken->getToken(),
                ]);
            } catch (CodeNotValidException $exception) {
                $form->get('code')->addError(new FormError(
                    $this->get('translator')->trans('validators.userPhone.validateCode.codeNotValid')
                ));
            } catch (CodeAlreadyValidatedException $exception) {
                return $this->redirectToRoute('event_user_event_token_confirm_agenda', [
                    'token' => $userEventToken->getToken(),
                ]);
            }
        }

        return $this->render('EventBundle:User/Phone:validateCodeWithToken.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'token' => $userEventToken->getToken(),
        ]);
    }

    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     * @param Sheet         $sheet
     * @param Participant   $participant
     *
     * @return RedirectResponse|Response
     */
    public function validateAction(
        Request $request,
        EventDomain $eventDomain,
        UserInterface $user = null,
        Sheet $sheet,
        Participant $participant
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(ValidateMobileAccessVoter::PERMISSION_NAME, $eventDomain->getEvent());

        $userEventPhone = $this
            ->get('repository.user.user_event_phone_repository')
            ->find($user, $eventDomain->getEvent())
        ;

        $validate = new ValidateCode($userEventPhone);
        $form     = $this->createForm(ValidateCodeType::class, $validate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($validate);
                $this->addFlash('confirm', 'flash.event.user_event_phone.validate.success');

                if ($redirectTo = $this->container->get('session')->getFlashBag()->get('redirectTo')) {
                    return $this->redirect($redirectTo[0]);
                }

                return $this->redirectToRoute('event_account_participant_profile', [
                    'sheet'       => $sheet->getId(),
                    'participant' => $participant->getId(),
                ]);
            } catch (CodeNotValidException $exception) {
                $form->get('code')->addError(new FormError(
                    $this->get('translator')->trans('validators.userPhone.validateCode.codeNotValid')
                ));
            } catch (CodeAlreadyValidatedException $exception) {
                return $this->redirectToRoute('event_user_phone_validate', [
                    'sheet'       => $sheet->getId(),
                    'participant' => $participant->getId(),
                ]);
            }
        }

        return $this->render('EventBundle:User/Phone:validateCode.html.twig', [
            'event'       => $eventDomain->getEvent(),
            'form'        => $form->createView(),
            'sheet'       => $sheet,
            'participant' => $participant,
        ]);
    }

    /**
     * @param EventDomain        $eventDomain
     * @param UserEventToken     $userEventToken
     * @param UserInterface|null $user
     *
     * @return Response|RedirectResponse
     */
    public function validateSuccessAction(
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ) {
        $event = $eventDomain->getEvent();

        $this->checkUserEventTokenAccess($event, $userEventToken, $user);

        $userEventPhone = $this
            ->get('repository.user.user_event_phone_repository')
            ->find($userEventToken->getUser(), $userEventToken->getEvent());

        if (null === $userEventPhone) {
            throw $this->createNotFoundException(
                sprintf('The user event phone does not exist for this user %s', $userEventToken->getUser()->getId())
            );
        }

        if (!$userEventPhone->isValidated()) {
            throw $this->createNotFoundException('The user event phone is not validated');
        }

        return $this->render('EventBundle:User/Phone:validateCodeSuccess.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Event              $event
     * @param UserEventToken     $userEventToken
     * @param UserInterface|null $user
     */
    private function checkUserEventTokenAccess(Event $event, UserEventToken $userEventToken, UserInterface $user = null)
    {
        if ($userEventToken->getEvent() !== $event || !$userEventToken->isAgendaConfirmation()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->get('adapter.authentication_manager')->disconnect();
        }
    }
}
