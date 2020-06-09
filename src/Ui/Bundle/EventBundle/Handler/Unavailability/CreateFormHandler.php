<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsWithUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\CreateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CreateFormHandler
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBus */
    private $commandBus;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface  $translator
     * @param CommandBus           $commandBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        CommandBus $commandBus
    ) {
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
    }

    /**
     * @param CreateForm $createForm
     *
     * @return CreateFormView
     */
    public function handle(CreateForm $createForm): CreateFormView
    {
        $request = $createForm->request;
        $event   = $createForm->event;
        $sheet = $createForm->sheet;
        $user = $createForm->user;
        $timezone = $createForm->timezone;

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $create = new Create($event, $sheet, $user, $request->getLocale(), $createForm->timezone);
        $form = $this->formFactory->create(CreateType::class, $create, [
            'action' => $createForm->actionUrl,
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'event' => $event,
            'locale' => $request->getLocale(),
            'sheet' => $sheet,
            'timezone' => $timezone,
        ]);

        // If the page is called by an ajax request, only show the form
        if ($request->isXmlHttpRequest()) {
            return new CreateFormView(CreateFormView::XML_HTTP_REQUEST_RESPONSE, $form->createView());
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);

                return new CreateFormView(CreateFormView::HANDLER_SUCCESS, $form->createView());
            } catch (NoParticipantSelectedException $exception) {
                $this->handleParticipantsError($form, $this->createNoParticipantSelectedExceptionError());
            } catch (ParticipantsWithUnavailabilityException $exception) {
                $this->handleParticipantsError($form, $this->participantsWithUnavailabilityExceptionError($exception));
            } catch (ParticipantsSelectedWithMeetingOrHappeningException $exception) {
                $this->handleParticipantsError(
                    $form,
                    $this->createParticipantsSelectedWithMeetingOrHappeningExceptionError($exception)
                );
            } catch (TimeOutOfRangeException $exception) {
                if ($exception->isOutOfRangeAtBeginOfDay()) {
                    $form->get('time')->get('begin')->addError(
                        new FormError(
                            $this->translator->trans(
                                'validators.unavailability.timeOutOfRange.begin',
                                [
                                    '%day%' => DayHelper::getFormatter($request->getLocale(), $timezone)
                                        ->format($exception->day->getBegin()),
                                ],
                                'validators'
                            )
                        )
                    );
                } else {
                    $form->get('time')->get('end')->addError(
                        new FormError(
                            $this->translator->trans(
                                'validators.unavailability.timeOutOfRange.end',
                                [
                                    '%day%' => DayHelper::getFormatter($request->getLocale(), $timezone)
                                        ->format($exception->day->getEnd()),
                                ],
                                'validators'
                            )
                        )
                    );
                }
            }

            return new CreateFormView(CreateFormView::HANDLER_ERROR, $form->createView());
        }

        return new CreateFormView(CreateFormView::CREATE_FORM, $form->createView());
    }

    /**
     * @param FormInterface $form
     * @param FormError     $formError
     */
    private function handleParticipantsError(FormInterface $form, FormError $formError): void
    {
        if ($form->has('participants')) {
            $form->get('participants')->addError($formError);
        } else {
            $form->addError($formError);
        }
    }

    /**
     * @param ParticipantsSelectedWithMeetingOrHappeningException $exception
     *
     * @return FormError
     */
    private function createParticipantsSelectedWithMeetingOrHappeningExceptionError(
        ParticipantsSelectedWithMeetingOrHappeningException $exception
    ): FormError {
        return new FormError(
            $this->translator->transChoice(
                'validators.unavailability.participantsWithConflict',
                $exception->getNumberOfConflict(),
                ['%participants%' => $exception->getListOfParticipantsName()],
                'validators'
            )
        );
    }

    /**
     * @param ParticipantsWithUnavailabilityException $participantsWithUnavailabilityException
     *
     * @return FormError
     */
    private function participantsWithUnavailabilityExceptionError(
        ParticipantsWithUnavailabilityException $participantsWithUnavailabilityException
    ): FormError {
        return new FormError(
            $this->translator->transChoice(
                'validators.unavailability.participantsWithUnavailability',
                \count($participantsWithUnavailabilityException->participantNames),
                ['%participants%' => $participantsWithUnavailabilityException->getListOfParticipantsName()],
                'validators'
            )
        );
    }

    /**
     * @return FormError
     */
    private function createNoParticipantSelectedExceptionError(): FormError
    {
        return new FormError(
            $this->translator->trans('validators.unavailability.participantsNotSelected', [], 'validators')
        );
    }
}
