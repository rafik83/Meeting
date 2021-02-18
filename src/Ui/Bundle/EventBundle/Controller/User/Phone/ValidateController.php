<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ValidateController extends AbstractController
{
    private TranslatorInterface $translator;
    private AuthenticationManagerInterface $authenticationManager;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        AuthenticationManagerInterface $authenticationManager,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->authenticationManager = $authenticationManager;
        $this->commandBus = $commandBus;
    }

    public function validateWithTokenAction(
        Request $request,
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ): Response {
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
                $this->commandBus->handle($validate);

                return $this->redirectToRoute('event_user_event_phone_validate_code_success', [
                    'token' => $userEventToken->getToken(),
                ]);
            } catch (CodeNotValidException $exception) {
                $form->get('code')->addError(new FormError(
                    $this->translator->trans('validators.userPhone.validateCode.codeNotValid')
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

    public function validateAction(
        Request $request,
        EventDomain $eventDomain,
        UserInterface $user = null,
        Sheet $sheet,
        Participant $participant
    ): Response {
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
                $this->commandBus->handle($validate);
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
                    $this->translator->trans('validators.userPhone.validateCode.codeNotValid')
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

    public function validateSuccessAction(
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ): Response {
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

    private function checkUserEventTokenAccess(Event $event, UserEventToken $userEventToken, UserInterface $user = null): void
    {
        if ($userEventToken->getEvent() !== $event || !$userEventToken->isAgendaConfirmation()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->authenticationManager->disconnect();
        }
    }
}
