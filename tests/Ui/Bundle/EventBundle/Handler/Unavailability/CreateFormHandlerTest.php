<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Unavailability;

use Doctrine\Common\Collections\ArrayCollection;
use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability\CreateType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability\CreateFormView;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class CreateFormHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $form;

    /** @var ObjectProphecy */
    private $formView;

    public function setUp()
    {
        $this->form = $this->prophesize(Form::class);
        $this->formView = $this->prophesize(FormView::class);
        $this->request = $this->prophesize(Request::class);
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);

        $this->participant->getUser()->willReturn($this->user->reveal());
        $this->sheet->getParticipants()->willReturn(new ArrayCollection([$this->participant->reveal()]));
        $this->sheet->getUserParticipant($this->user->reveal())->willReturn($this->participant->reveal());
        $this->request->getLocale()->willReturn('fr');
        $this->form->createView()->willReturn($this->formView);

        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
    }

    public function testHandleXmlHttpRequest()
    {
        $expected = new CreateFormView(CreateFormView::XML_HTTP_REQUEST_RESPONSE, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(true);

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleValidForm()
    {
        $expected = new CreateFormView(CreateFormView::HANDLER_SUCCESS, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($command)->shouldBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleNoParticipantSelectedException()
    {
        $expected = new CreateFormView(CreateFormView::HANDLER_ERROR, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);

        $exception = $this->prophesize(NoParticipantSelectedException::class);
        $this->commandBus->handle($command)->shouldBeCalled()->willThrow($exception->reveal());
        $this->translator
            ->trans('validators.unavailability.participantsNotSelected', [], 'validators')
            ->shouldBeCalled()
            ->willReturn('participantsNotSelected')
        ;
        $this->form->has('participants')->shouldBeCalled()->willReturn(true);
        $this->form->get('participants')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->addError(new FormError('participantsNotSelected'))->shouldBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleParticipantsSelectedWithMeetingOrHappeningException()
    {
        $expected = new CreateFormView(CreateFormView::HANDLER_ERROR, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);

        $exception = $this->prophesize(ParticipantsSelectedWithMeetingOrHappeningException::class);
        $exception->getNumberOfConflict()->willReturn(3);
        $exception->getListOfParticipantsName()->willReturn('pierre,paul,jack');
        $this->commandBus->handle($command)->shouldBeCalled()->willThrow($exception->reveal());
        $this->translator
            ->transChoice(
                'validators.unavailability.participantsWithConflict',
                3,
                ['%participants%' => 'pierre,paul,jack'],
                'validators'
            )
            ->shouldBeCalled()
            ->willReturn('participantsSelectedWithMeetingOrHappening')
        ;
        $this->form->has('participants')->shouldBeCalled()->willReturn(true);
        $this->form->get('participants')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->addError(new FormError('participantsSelectedWithMeetingOrHappening'))->shouldBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleTimeOutOfRangeException()
    {
        $this->event->getTimeZone()->willReturn('Europe/Paris');
        $day = $this->prophesize(Event\Day::class);
        $day->getDay()->willReturn(new \DateTime('2017-10-10 10:10:10.000'));
        $this->event->getFirstDay()->willReturn($day->reveal());
        $expected = new CreateFormView(CreateFormView::HANDLER_ERROR, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);

        $exception = new TimeOutOfRangeException($day->reveal(), TimeOutOfRangeException::BEGIN);
        $this->commandBus->handle($command)->shouldBeCalled()->willThrow($exception);
        $this->translator
            ->trans(
                'validators.unavailability.timeOutOfRange.begin',
                ['%day%' => 'mardi 10 octobre 2017'],
                'validators'
            )
            ->shouldBeCalled()
            ->willReturn('time-begin-out-of-range')
        ;
        $this->form->get('time')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->get('begin')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->addError(new FormError('time-begin-out-of-range'))->shouldBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleTimeOutOfRangeEndOfDayException()
    {
        $this->event->getTimeZone()->willReturn('Europe/Paris');
        $day = $this->prophesize(Event\Day::class);
        $day->getDay()->willReturn(new \DateTime('2017-10-10 10:10:10.000'));
        $this->event->getFirstDay()->willReturn($day->reveal());
        $expected = new CreateFormView(CreateFormView::HANDLER_ERROR, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $this->form->isValid()->shouldBeCalled()->willReturn(true);

        $exception = new TimeOutOfRangeException($day->reveal(), TimeOutOfRangeException::END);
        $this->commandBus->handle($command)->shouldBeCalled()->willThrow($exception);
        $this->translator
            ->trans(
                'validators.unavailability.timeOutOfRange.end',
                ['%day%' => 'mardi 10 octobre 2017'],
                'validators'
            )
            ->shouldBeCalled()
            ->willReturn('time-end-out-of-range')
        ;
        $this->form->get('time')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->get('end')->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->addError(new FormError('time-end-out-of-range'))->shouldBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $expected = new CreateFormView(CreateFormView::CREATE_FORM, $this->formView->reveal());

        $command = new Create(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            'Europe/Paris'
        );
        $this->formFactory->create(CreateType::class, $command, [
            'action'                 => 'action_url',
            'isUserAloneParticipant' => true,
            'event'                  => $this->event->reveal(),
            'locale'                 => 'fr',
            'sheet'                  => $this->sheet->reveal(),
            'timezone'               => 'Europe/Paris',
        ])->shouldBeCalled()->willReturn($this->form->reveal());

        $this->request->isXmlHttpRequest()->willReturn(false);
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $this->form->isValid()->shouldNotBeCalled();

        $this->commandBus->handle($command)->shouldNotBeCalled();

        $handler = new CreateFormHandler(
            $this->formFactory->reveal(),
            $this->translator->reveal(),
            $this->commandBus->reveal()
        );
        $result = $handler->handle(
            new CreateForm(
                $this->request->reveal(),
                $this->event->reveal(),
                $this->sheet->reveal(),
                $this->user->reveal(),
                'action_url',
                'Europe/Paris'
            )
        );

        $this->assertEquals($expected, $result);
    }
}
