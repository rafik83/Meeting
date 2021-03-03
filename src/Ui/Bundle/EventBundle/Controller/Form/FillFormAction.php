<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileException;
use Proximum\Vimeet\Application\Command\Template\Form\FillStepCommand;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Participant\Sheet\ParticipantListViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQuery;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\Form\FillStepType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class FillFormAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationChecker = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        FormTemplate $formTemplate,
        int $step
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$sheet->hasParticipant($participant)
        ) {
            throw new AccessDeniedException();
        }

        if (!$formTemplate->isPublished() || !$formTemplate->hasType($sheet->getType())) {
            throw new AccessDeniedException('This form is not available.');
        }

        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();

        $formTemplateTitle = $formTemplate->getTranslatedTitle($locale);

        try {
            /** @var BlockStepView $blockStepView */
            $blockStepView = $this->queryBus->handle(
                new FillStepQuery($formTemplate, $sheet, $participant, $locale, $step)
            );
        } catch (BlockForGivenStepNotFoundException $exception) {
            return new RedirectResponse($this->router->generate('event'));
        } catch (GivenStepIsRequiredAndNotFilledException $exception) {
            return new RedirectResponse($this->router->generate('event_participant_fill_form', [
                'formTemplate' => $formTemplate->getId(),
                'sheet' => $sheet->getId(),
                'participant' => $participant->getId(),
                'step' => $exception->step,
            ]));
        }

        $form = $this->formFactory->create(FillStepType::class, $blockStepView->block, [
            'blockStepView' => $blockStepView,
            'country' => $event->getCountry(),
            'locale' => $locale,
            'locales' => $event->getLocales(),
            'isAdmin' => $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->handleData(
                $participant->getUser(),
                $sheet,
                $blockStepView->block,
                $form,
                $blockStepView->block->getData()
            );

            if ($form->isValid()) {
                $command = new FillStepCommand($formTemplate, $sheet, $participant, $blockStepView);
                $this->commandBus->handle($command);

                if ($blockStepView->getCurrentStepIndex() === $blockStepView->getTotalNumberOfStep()) {
                    $this->flashBag->add('success', 'flash.form_template.fill_step_finished.success');

                    return new RedirectResponse($this->router->generate('event'));
                }

                return new RedirectResponse($this->router->generate('event_participant_fill_form', [
                    'formTemplate' => $formTemplate->getId(),
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'step' => $blockStepView->getCurrentStepIndex() + 1,
                ]));
            }
        }

        $breadCrumb = null;

        return new Response(
            $this->twig->render(
                '@Event/Form/fillForm.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'participantList' => $this->queryBus->handle(new ParticipantListViewQuery($sheet, $participant, $locale)),
                    'formTemplate' => $formTemplate,
                    'blockStepView' => $blockStepView,
                    'formTemplateTitle' => $formTemplateTitle,
                    'form' => $form->createView(),
                ]
            )
        );
    }

    private function handleData(
        User $user,
        Sheet $sheet,
        Block $block,
        FormInterface $form,
        array $data
    ): void {
        $data = array_filter($data, function ($value) { return null !== $value; });

        $uploadedAndImageObjects = $block->getUploadAndImageObjects();

        /**
         * @var string $key
         * @var TemplateObject $object
         */
        foreach ($uploadedAndImageObjects as $key => $object) {
            if ($form->has($key) && null !== $form->get($key)->get('file')->getData()) {
                $file = $form->get($key)->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    try {
                        $data = $this->commandBus->handle(
                            new UploadFile(
                                $sheet,
                                $user,
                                $object,
                                $data,
                                $object->hasTag(Tag::SHEET_DATA)
                            )
                        );
                        $object->setData($data[$key]);
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
    }
}
