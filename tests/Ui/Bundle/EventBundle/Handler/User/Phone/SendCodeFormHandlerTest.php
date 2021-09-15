<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone\SendCodeType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SendCodeFormHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $tipTranslationView;

    /** @var ObjectProphecy */
    private $form;

    /** @var ObjectProphecy */
    private $userEventPhoneRepository;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var SendCodeFormHandler */
    private $sendCodeFormHandler;

    /** @var RouterInterface */
    private $router;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->request->getLocale()->willReturn('fr');

        $this->user = $this->prophesize(User::class);
        $this->user->getMobile()->willReturn('+3312345678');

        $this->event = $this->prophesize(Event::class);
        $this->event->getCountry()->willReturn('FR');

        $this->tipTranslationView = $this->prophesize(TipTranslationView::class);

        $this->form = $this->prophesize(FormInterface::class);
        $this->form->handleRequest($this->request->reveal())->willReturn($this->form);

        $this->userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $this->formFactory = $this->prophesize(FormFactory::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->sendCodeFormHandler = new SendCodeFormHandler(
            $this->userEventPhoneRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal()
        );
    }

    public function testShowForm()
    {
        // form is not submitted
        $this->form->isSubmitted()->willReturn(false);

        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->commandBus
            ->handle(
                new TipTranslationViewByUserQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn([$this->tipTranslationView])
        ;

        $sendCode = new SendCode($this->user->reveal(), $this->event->reveal(), '+3312345678', 'fr');
        $this->formFactory
            ->create(SendCodeType::class, $sendCode, [
                'country' => 'FR',
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($this->form)
        ;

        $this->commandBus
            ->handle($sendCode)
            ->shouldNotBeCalled()
        ;

        $result = $this->sendCodeFormHandler->handle(
            new SendCodeForm($this->request->reveal(), $this->user->reveal(), $this->event->reveal())
        );
        $expected = new SendCodeView(
            SendCodeView::SEND_CODE_SHOW_FORM,
            $this->form->reveal(),
            [$this->tipTranslationView->reveal()]
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleForm()
    {
        // form is submitted and is valid
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->commandBus
            ->handle(
                new TipTranslationViewByUserQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn([$this->tipTranslationView])
        ;

        $sendCode = new SendCode($this->user->reveal(), $this->event->reveal(), '+3312345678', 'fr');
        $this->formFactory
            ->create(SendCodeType::class, $sendCode, [
                'country' => 'FR',
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($this->form)
        ;

        $this->commandBus
            ->handle($sendCode)
            ->shouldBeCalled()
        ;

        $result = $this->sendCodeFormHandler->handle(
            new SendCodeForm($this->request->reveal(), $this->user->reveal(), $this->event->reveal())
        );
        $expected = new SendCodeView(SendCodeView::SEND_CODE_SUCCESS, null, []);

        $this->assertEquals($expected, $result);
    }

    public function testNoTipAndNotShownForm()
    {
        $this->commandBus
            ->handle(
                new TipTranslationViewByUserQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->formFactory
            ->create()
            ->shouldNotBeCalled()
        ;

        $result = $this->sendCodeFormHandler->handle(
            new SendCodeForm($this->request->reveal(), $this->user->reveal(), $this->event->reveal())
        );
        $expected = new SendCodeView(SendCodeView::SEND_CODE_FORM_NOT_SHOWN, null, []);

        $this->assertEquals($expected, $result);
    }

    public function testPhoneAlreadyValidatedAndNotShownForm()
    {
        $userEventPhone = $this->prophesize(User\UserEventPhone::class);
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone->reveal())
        ;

        // There is no tip for this context
        $this->commandBus
            ->handle(
                new TipTranslationViewByUserQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    'fr'
                )
            )
            ->shouldNotBeCalled()
        ;

        $this->formFactory
            ->create()
            ->shouldNotBeCalled()
        ;

        $result = $this->sendCodeFormHandler->handle(
            new SendCodeForm($this->request->reveal(), $this->user->reveal(), $this->event->reveal())
        );
        $expected = new SendCodeView(SendCodeView::SEND_CODE_FORM_NOT_SHOWN, null, []);

        $this->assertEquals($expected, $result);
    }

    public function testInvalidReceiverException()
    {
        // form is submitted and is valid
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->commandBus
            ->handle(
                new TipTranslationViewByUserQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                    'fr'
                )
            )
            ->shouldBeCalled()
            ->willReturn([$this->tipTranslationView])
        ;

        $sendCode = new SendCode($this->user->reveal(), $this->event->reveal(), '+3312345678', 'fr');
        $this->formFactory
            ->create(SendCodeType::class, $sendCode, [
                'country' => 'FR',
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($this->form)
        ;

        $this->commandBus
            ->handle($sendCode)
            ->shouldBeCalled()
            ->willThrow(InvalidReceiverException::class)
        ;

        $this->form->get('phone')->shouldBeCalled()->willReturn($this->form);

        $this->form
            ->addError(new FormError('validators.send_code.error.invalidReceiver'))
            ->shouldBeCalled()
        ;

        $result = $this->sendCodeFormHandler->handle(
            new SendCodeForm($this->request->reveal(), $this->user->reveal(), $this->event->reveal())
        );
        $expected = new SendCodeView(
            SendCodeView::SEND_CODE_SHOW_FORM,
            $this->form->reveal(),
            [$this->tipTranslationView->reveal()]
        );

        $this->assertEquals($expected, $result);
    }
}
