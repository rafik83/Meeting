<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Register\ParticipantStep;
use Proximum\Vimeet\Application\Command\Register\RegisterNewUser;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Register\RegisterNewUserType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\BlockType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /**
     * Register an account.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param TypeView  $typeView
     *
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $command = new Email();
        $form    = $this->createForm(EmailType::class, $command, ['action' => $request->getUri()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($command->email);

            if ($user) {
                if ($this->hasSheets($user, $eventView)) {
                    $this->addFlash('success', 'flash.event.register.already_known.login');
                } else {
                    $this->setFlashRegisterType($typeView->id);
                    $this->addFlash('success', 'flash.event.register.already_known.message');
                }

                $this->setFlashLoginEmail($command->email);

                return $this->redirectToRoute('event_login_second_step');
            }

            $this->setFlashRegisterEmail($command->email);

            return $this->redirectToRoute('event_register_new_user', ['typeView' => $typeView->id]);
        }

        return $this->render('EventBundle:Register:register.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Register an account.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param TypeView  $typeView
     *
     * @return RedirectResponse|Response
     */
    public function registerNewUserAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        $command = new RegisterNewUser($this->getFlashEmail(), $request->getLocale());

        if ($command->email === null || $this->emailExists($command->email)) {
            return $this->redirectToRoute('event_register', ['typeView' => $typeView->id]);
        }

        $this->setFlashRegisterEmail($command->email);

        $form = $this->createForm(RegisterNewUserType::class, $command, ['action' => $request->getUri()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->get('tactician.commandbus')->handle($command);
                $this->get('adapter.authentication_manager')->authenticate($result->user, 'main');

                return $this->redirectToRoute('event_participate', ['typeView'  => $typeView->id]);
            } catch (EmailAlreadyExistsException $exception) {
                $this->resetFlashRegisterEmail();

                return $this->redirectToRoute('event_login');
            }
        }

        return $this->render('EventBundle:Register:registerNewUser.html.twig', [
            'email'     => $command->email,
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Create a participation to an event.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param TypeView  $typeView
     *
     * @return RedirectResponse|Response
     */
    public function participateAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if the user has already created a participate
        $this->hasUserAlreadyCreatedParticipant($eventView->getId(), $this->getUser()->getId());

        $locale               = $request->getLocale();
        $event                = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);
        $type                 = $this->get('vimeet_infrastructure.repository.type_repository')->getById($typeView->id);
        $registrationTemplate = $this->get('template.template_data_factory')->createRegistrationFromType($type, $locale);
        $user                 = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($this->getUser()->getEmail());
        $registrationTemplate = $this->get('account.synchronizer')->get($registrationTemplate, $user);
        $participantBlock     = $registrationTemplate->getFirstBlock();

        $form = $this->createForm(BlockType::class, $participantBlock, [
            'block'   => $participantBlock,
            'locale'  => $locale,
            'country' => $event->getCountry(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data        = $this->handleData($registrationTemplate, $form, $participantBlock->getData());
            $participate = new Participate($this->getUser(), $event, $type, $locale, $data, $registrationTemplate);
            $this->get('tactician.commandbus')->handle($participate);

            $nextStep = $registrationTemplate->getNextBlockPosition(1);

            return $nextStep
                ? $this->redirectToRoute('event_participant_step', ['step' => 1, 'participant' => $participate->participant->getId()])
                : $this->redirectToRoute('event_sheet');
        }

        return $this->render('EventBundle:Register:participate.html.twig', [
            'eventView'  => $eventView,
            'typeView'   => $typeView,
            'form'       => $form->createView(),
            'stepTitle'  => $participantBlock->getTitle($locale),
            'stepsCount' => $registrationTemplate->getBlocksCount(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Participant $participant
     * @param int         $step
     *
     * @return RedirectResponse|Response
     */
    public function participantStepAction(Request $request, EventView $eventView, Participant $participant, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($participant->getSheet()->getEvent()->getId() !== $eventView->getId()) {
            throw $this->createAccessDeniedException('Participation does not exist');
        }

        if ($participant->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('The user does not match the particpant.');
        }

        $locale               = $request->getLocale();
        $registrationTemplate = $this->get('template.template_data_factory')->createRegistrationFromParticipant($participant, $locale);
        $registrationTemplate = $this->get('account.synchronizer')->get($registrationTemplate, $participant->getUser());
        $participantBlock     = $registrationTemplate->getBlock(intval($step));

        if (null === $participantBlock) {
            throw $this->createNotFoundException('Unknown step');
        }

        $data = ['block' => $participantBlock, 'locale' => $locale, 'country' => $eventView->country];
        $form = $this->createForm(BlockType::class, $participantBlock, $data);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data            = $this->handleData($registrationTemplate, $form, $participantBlock->getData());
            $participantStep = new ParticipantStep($registrationTemplate, $participant, $step, $locale, $data);
            $this->get('tactician.commandbus')->handle($participantStep);

            $nextStep = $registrationTemplate->getNextBlockPosition($step);

            return $nextStep
                ? $this->redirectToRoute('event_participant_step', ['step' => $nextStep, 'participant' => $participant->getId()])
                : $this->redirectToRoute('event_sheet');
        }

        $participantCard = $this->get('tactician.commandbus.query')->handle(new CardViewQuery($participant, $locale));

        return $this->render('EventBundle:Register:participateStep.html.twig', [
            'eventView'       => $eventView,
            'form'            => $form->createView(),
            'stepsCount'      => $registrationTemplate->getBlocksCount(),
            'stepNumber'      => $step,
            'stepTitle'       => $participantBlock->getTitle($locale),
            'participantCard' => $participantCard,
        ]);
    }

    /**
     * @param int $eventId
     * @param int $userId
     */
    private function hasUserAlreadyCreatedParticipant($eventId, $userId)
    {
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAllParticipantForUser($eventId, $userId);

        if (1 <= count($participants)) {
            throw $this->createAccessDeniedException('Participation already created');
        }
    }

    /**
     * @param TemplateData  $registrationTemplate
     * @param FormInterface $form
     * @param array         $data
     *
     * @return array
     */
    private function handleData(TemplateData $registrationTemplate, FormInterface $form, array $data)
    {
        $data = array_filter($data, function ($value) { return null !== $value; });

        $imageObjects = $registrationTemplate->getImageObjects();
        $fileStorage  = $this->get('adapter.local_file_storage');

        foreach ($imageObjects as $key => $object) {
            if ($form->has($key) && $form->get($key)->get('file')->getData() !== null) {
                $file = $form->get($key)->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $data[$key]['image'] = $fileStorage->remove($object->getContentValue())->upload($file);
                } else {
                    $form->get($key)->get('file')->addError(new FormError('validators.field.notValid.image'));
                }
            }
        }

        return $data;
    }

    /**
     * @return string|null
     */
    private function getFlashEmail()
    {
        $emails = $this->container->get('session')->getFlashBag()->get('register_email');

        return array_shift($emails);
    }

    /**
     * @param string $email
     *
     * @return RegisterController
     */
    protected function setFlashRegisterEmail($email)
    {
        $this->resetFlashRegisterEmail()->addFlash('register_email', $email);

        return $this;
    }

    /**
     * @return RegisterController
     */
    protected function resetFlashRegisterEmail()
    {
        $this->container->get('session')->getFlashBag()->get('register_email');

        return $this;
    }

    /**
     * @param string $email
     *
     * @return RegisterController
     */
    protected function setFlashLoginEmail($email)
    {
        $this->resetFlashLoginEmail()->addFlash('login_email', $email);

        return $this;
    }

    /**
     * @return RegisterController
     */
    protected function resetFlashLoginEmail()
    {
        $this->container->get('session')->getFlashBag()->get('login_email');

        return $this;
    }

    /**
     * @param $email
     *
     * @return bool
     */
    protected function emailExists($email)
    {
        return $this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email);
    }

    /**
     * @return RegisterController
     */
    protected function resetFlashRegisterType()
    {
        $this->container->get('session')->getFlashBag()->get('register_type');

        return $this;
    }

    /**
     * @param int $id
     *
     * @return RegisterController
     */
    protected function setFlashRegisterType($id)
    {
        $this->resetFlashRegisterType()->addFlash('register_type', $id);

        return $this;
    }

    /**
     * @param User      $user
     * @param EventView $eventView
     *
     * @return bool
     */
    protected function hasSheets(User $user, EventView $eventView)
    {
        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetByUserAndEvent($user, $eventView);

        return !empty($sheets);
    }
}
