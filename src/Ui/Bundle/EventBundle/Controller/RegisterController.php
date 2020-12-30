<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileException;
use Proximum\Vimeet\Application\Command\Register\ParticipantStep;
use Proximum\Vimeet\Application\Command\Register\RegisterNewUser;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Register\PreFillUserData;
use Proximum\Vimeet\Application\View\Register\PreFillUserDataView;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Exception\UploadNotAllowedOnFirstStepOfRegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Domain\User\Security\CanPasswordBeDefinedWithActivationEmail;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Register\RegisterNewUserType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\BlockType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Proximum\Vimeet\Ui\Flash\TransMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RegisterController extends Controller
{
    /**
     * Register an account.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param TypeView    $typeView
     *
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, EventDomain $eventDomain, TypeView $typeView)
    {
        $event = $eventDomain->getEvent();

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->hasSheets($this->getUser(), $eventDomain->getEvent())) {
                return $this->redirectToRoute('event');
            } else {
                return $this->redirectToRoute('event_participate', ['typeView' => $typeView->id]);
            }
        }

        $response = $this
            ->get('infrastructure.route.home_dispatch.home_user_dispatcher')
            ->attemptDispatchUser($eventDomain->getEvent(), $this->getUser());

        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $this->setFlashRegisterType($typeView->id);
        $command = new Email();
        $form    = $this->createForm(EmailType::class, $command, ['action' => $request->getUri()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $command->email = StringHelper::trimSpacesAndNonBreakSpaces($command->email);

            if ($this->get(CanPasswordBeDefinedWithActivationEmail::class)->isSatisfiedBy($event, $command->email)) {
                $this->addFlash('login_email', $command->email);

                return $this->redirectToRoute('event_login_send_activation_mail');
            }

            $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($command->email);

            if ($user) {
                if ($this->hasSheets($user, $event)) {
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
            'form' => $form->createView(),
            'event' => $event,
            'typeView' => $typeView,
            'typeDescription' => $this->get('markdown')->toHtml($typeView->description),
        ]);
    }

    /**
     * Register an account.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param TypeView    $typeView
     *
     * @return RedirectResponse|Response
     */
    public function registerNewUserAction(Request $request, EventDomain $eventDomain, TypeView $typeView)
    {
        $command = new RegisterNewUser(
            StringHelper::trimSpacesAndNonBreakSpaces($this->getFlashEmail()),
            $request->getLocale(),
            $eventDomain->getEvent(),
            $typeView
        );

        if ('' === $command->email || $this->emailExists($command->email)) {
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
            'email' => $command->email,
            'form' => $form->createView(),
            'event' => $eventDomain->getEvent(),
            'typeView' => $typeView,
            'typeDescription' => $this->get('markdown')->toHtml($typeView->description),
        ]);
    }

    /**
     * Create a participation to an event.
     *
     * @param Request         $request
     * @param EventDomain     $eventDomain
     * @param TypeView        $typeView
     * @param UserDomain|null $userDomain
     *
     * @return Response
     */
    public function participateAction(
        Request $request,
        EventDomain $eventDomain,
        TypeView $typeView,
        UserDomain $userDomain = null
    ): Response {
        $event = $eventDomain->getEvent();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->hasUserAlreadyCreatedParticipant($event, $userDomain->getUser());

        $response = $this
            ->get('infrastructure.route.home_dispatch.home_user_dispatcher')
            ->attemptDispatchUser($event, $userDomain->getUser());

        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $locale = $request->getLocale();

        $type = $this->get('vimeet_infrastructure.repository.type_repository')
            ->getById($typeView->id);

        if (!$type instanceof Type) {
            return $this->redirectToRoute('event');
        }

        $user = $userDomain->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('event');
        }

        $registrationTemplate = $this->get('template.template_data_factory')
            ->createRegistrationFromType($type, $locale);

        /** @var PreFillUserDataView $preFillUserDataView */
        $preFillUserDataView = $this->get('tactician.commandbus.query')->handle(
            new PreFillUserData(
                $user,
                $event,
                $registrationTemplate,
                $locale
            )
        );

        $registrationTemplateFirstStep = $registrationTemplate->getFirstBlock();

        // Add or update UserEvent type
        $this->get('components.user.type_resolver')->resolve($user, $event, $type);

        $form = $this->createForm(BlockType::class, $registrationTemplateFirstStep, [
            'block' => $registrationTemplateFirstStep,
            'locale' => $locale,
            'locales' => $event->getLocales(),
            'country' => $event->getCountry(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = $this->handleData(
                $user,
                null,
                $registrationTemplate,
                $form,
                $registrationTemplateFirstStep->getData()
            );

            $participate = new Participate(
                $userDomain->getUser(),
                $event,
                $type,
                $locale,
                $data,
                $registrationTemplate
            );

            $this->get('tactician.commandbus')->handle($participate);

            $nextStep = $registrationTemplate->getNextBlockPosition(1);

            if ($nextStep) {
                return $this->redirectToRoute('event_participant_step', [
                    'step' => $nextStep,
                    'participant' => $participate->participant->getId(),
                ]);
            }

            $this->container->get('session')->getFlashBag()->set('first_registration', true);

            return $this->redirectToRoute('event_sheet_default', ['sheet' => $participate->sheet->getId()]);
        }

        if ($preFillUserDataView->isParticipationDataPreFilled()) {
            $this->addFlash('success', new TransMessage('flash.register.participationData.prefilled', []));
        }

        return $this->render('EventBundle:Register:participate.html.twig', [
            'event'           => $eventDomain->getEvent(),
            'typeView'        => $typeView,
            'form'            => $form->createView(),
            'stepTitle'       => $registrationTemplateFirstStep->getTitle($locale),
            'stepDescription' => $this->get('markdown')
                ->toHtml($registrationTemplateFirstStep->getDescription($locale)),
            'stepsCount'      => $registrationTemplate->getBlocksCount(),
        ]);
    }

    /**
     * @param Request         $request
     * @param EventDomain     $eventDomain
     * @param UserDomain|null $userDomain
     * @param Participant     $participant
     * @param int             $step
     *
     * @return RedirectResponse|Response
     */
    public function participantStepAction(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain = null,
        Participant $participant,
        $step
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessIfWrongParticipant($eventDomain, $userDomain, $participant);

        $locale = $request->getLocale();
        $event = $eventDomain->getEvent();
        $registrationTemplate = $this->get('template.template_data_factory')
            ->createRegistrationFromParticipant($participant, $locale);

        // pre-fill user participation data and update registration template with data
        /** @var PreFillUserDataView $preFillUserDataView */
        $preFillUserDataView = $this->get('tactician.commandbus.query')->handle(
            new PreFillUserData(
                $participant->getUser(),
                $eventDomain->getEvent(),
                $registrationTemplate,
                $locale
            )
        );

        if ($preFillUserDataView->isParticipationDataPreFilled()) {
            $participant->setData($preFillUserDataView->templateData->getData());
        }

        $registrationTemplateStep = $registrationTemplate->getBlock((int) $step);

        if (null === $registrationTemplateStep) {
            throw $this->createNotFoundException('Unknown step');
        }

        $options = [
            'block' => $registrationTemplateStep,
            'locale' => $locale,
            'country' => $event->getCountry(),
            'locales' => $event->getLocales(),
        ];

        $form = $this->createForm(BlockType::class, $registrationTemplateStep, $options);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = $this->handleData(
                $userDomain->getUser(),
                $participant->getSheet(),
                $registrationTemplate,
                $form,
                $registrationTemplateStep->getData()
            );

            if ($form->isValid()) {
                $participantStep = new ParticipantStep(
                    $registrationTemplate,
                    $participant,
                    $step,
                    $locale,
                    $data
                );
                $this->get('tactician.commandbus')->handle($participantStep);

                $nextStep = $registrationTemplate->getNextBlockPosition($step);

                if ($nextStep) {
                    return $this->redirectToRoute(
                        'event_participant_step',
                        ['step' => $nextStep, 'participant' => $participant->getId()]
                    );
                }

                $this->container->get('session')->getFlashBag()->add('first_registration', true);

                return $this->redirectToRoute('event_sheet_default', [
                    'sheet' => $participant->getSheet()->getId(),
                ]);
            }
        }

        $participantCard = $this->get('tactician.commandbus.query')->handle(
            new CardViewQuery($participant, $locale)
        );

        return $this->render('EventBundle:Register:participateStep.html.twig', [
            'event'           => $event,
            'form'            => $form->createView(),
            'stepsCount'      => $registrationTemplate->getBlocksCount(),
            'stepNumber'      => $step,
            'stepTitle'       => $registrationTemplateStep->getTitle($locale),
            'stepDescription' => $this->get('markdown')
                ->toHtml($registrationTemplateStep->getDescription($locale)),
            'participant'     => $participant,
            'participantCard' => $participantCard,
        ]);
    }

    /**
     * Check if the user has already created a participate
     *
     * @param Event $event
     * @param User  $user
     */
    private function hasUserAlreadyCreatedParticipant($event, $user)
    {
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAllParticipantForUser($event, $user);

        if (1 <= \count($participants)) {
            throw $this->createAccessDeniedException('Participation already created');
        }
    }

    private function handleData(
        User $user,
        ?Sheet $sheet,
        TemplateData $registrationTemplate,
        FormInterface $form,
        array $data
    ) {
        $data = array_filter($data, function ($value) { return null !== $value; });

        if (\count($registrationTemplate->getFirstBlock()->getUploadAndImageObjects())) {
            throw new UploadNotAllowedOnFirstStepOfRegistrationTemplateException(
                'Upload not allowed on first step of registration template'
            );
        }

        if (null === $sheet) {
            return $data;
        }

        $uploadedAndImageObjects = $registrationTemplate->getUploadAndImageObjects();

        foreach ($uploadedAndImageObjects as $key => $object) {
            if ($form->has($key) && null !== $form->get($key)->get('file')->getData()) {
                $file = $form->get($key)->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    try {
                        $data = $this->get('tactician.commandbus')->handle(
                            new UploadFile(
                                $sheet,
                                $user,
                                $object,
                                $data,
                                $object->hasTag(Tag::SHEET_DATA)
                            )
                        );
                    } catch (UploadFileException $exception) {
                        $form->get($key)->get('file')->addError(new FormError($exception->getMessage()));
                    }
                } else {
                    $form->get($key)->get('file')->addError(
                        new FormError(
                            sprintf(
                                'validators.field.notValid.%s',
                                $object instanceof UploadObject ? 'uploadObject' : 'image'
                            )
                        )
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getFlashEmail(): string
    {
        $emails = $this->container->get('session')->getFlashBag()->get('register_email');

        $email = array_shift($emails);

        return $email ?? '';
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
     * @param User  $user
     * @param Event $event
     *
     * @return bool
     */
    protected function hasSheets(User $user, Event $event)
    {
        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetsByUserAndEvent($user, $event);

        return !empty($sheets);
    }

    /**
     * Deny access if the participant does not match the user and the event
     *
     * @param EventDomain $eventDomain
     * @param UserDomain  $userDomain
     * @param Participant $participant
     *
     * @throws \LogicException
     * @throws AccessDeniedException
     */
    protected function denyAccessIfWrongParticipant(
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Participant $participant
    ) {
        if ($participant->getSheet()->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createAccessDeniedException('Participation does not exist');
        }

        if ($participant->getUser() !== $userDomain->getUser()) {
            throw $this->createAccessDeniedException('The user does not match the particpant.');
        }
    }
}
