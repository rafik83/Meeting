<?php

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Batch;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\EmailingSenderInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByType;
use Proximum\Vimeet\Application\Command\Messaging\Batch\SendEmailingByTypeHandler;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Components\Messaging\MessageFactory;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionResult;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareBatchSheetMailView;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message as MailMessage;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;

class SendEmailingByTypeHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet1;

    /** @var ObjectProphecy */
    private $sheet2;

    /** @var ObjectProphecy */
    private $sheet3;

    /** @var ObjectProphecy */
    private $messageFactory;

    /** @var ObjectProphecy */
    private $billingInfoRepository;

    /** @var ObjectProphecy */
    private $messageRepository;

    /** @var ObjectProphecy */
    private $mailSender;

    /** @var ObjectProphecy */
    private $substitutionHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ObjectProphecy */
    private $sheetIndexer;

    /** @var SendEmailingByTypeHandler */
    private $sendEmailingByTypeHandler;
    private $user1;
    private $user2;
    private $user3;
    private $type1;
    private $type2;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->event->getAvailableLocale('en')->willReturn('en');

        $this->type1 = $this->prophesize(Type::class);
        $this->type1->getId()->willReturn(11);

        $this->type2 = $this->prophesize(Type::class);
        $this->type2->getId()->willReturn(22);

        $this->user1 = $this->prophesize(User::class);
        $this->user1->getEmail()->willReturn('user1@example.net');
        $this->user1->getLocale()->willReturn('en');

        $this->sheet1 = $this->prophesize(Sheet::class);
        $this->sheet1->getId()->willReturn(1);
        $this->sheet1->getOwner()->willReturn($this->user1->reveal());
        $this->sheet1->getType()->willReturn($this->type1->reveal());

        $this->user2 = $this->prophesize(User::class);
        $this->user2->getEmail()->willReturn('user2@example.net');
        $this->user2->getLocale()->willReturn('fr');

        $this->sheet2 = $this->prophesize(Sheet::class);
        $this->sheet2->getId()->willReturn(2);
        $this->sheet2->getOwner()->willReturn($this->user2->reveal());
        $this->sheet2->getType()->willReturn($this->type2->reveal());

        $this->user3 = $this->prophesize(User::class);
        $this->user3->getEmail()->willReturn('user3@example.net');
        $this->user3->getLocale()->willReturn('fr');

        $this->sheet3 = $this->prophesize(Sheet::class);
        $this->sheet3->getId()->willReturn(3);
        $this->sheet3->getOwner()->willReturn($this->user3->reveal());
        $this->sheet3->getType()->willReturn($this->type1->reveal());

        $this->billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $this->messageFactory = $this->prophesize(MessageFactory::class);
        $this->messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $this->mailSender = $this->prophesize(EmailingSenderInterface::class);
        $this->substitutionHandler = $this->prophesize(SubstitutionHandler::class);
        $this->dateTime = new \DateTime();
        $this->sheetIndexer = $this->prophesize(SheetIndexerInterface::class);

        $this->sendEmailingByTypeHandler = new SendEmailingByTypeHandler(
            $this->billingInfoRepository->reveal(),
            $this->messageFactory->reveal(),
            $this->messageRepository->reveal(),
            $this->mailSender->reveal(),
            $this->substitutionHandler->reveal(),
            $this->dateTime,
            $this->sheetIndexer->reveal()
        );
    }

    public function test_send_both_default_and_custom_messages()
    {
        $messageId = 'sheet.validated';
        $mailType = 'mail_sheet_validated';

        $defaultMessage = $this->prophesize(Message::class);
        $defaultMessage->getName()->shouldBeCalled()->willReturn($mailType);
        $defaultMessage->isSendToEmailTeam()->willReturn(true);
        $defaultMessage->isSendEmailToBillingInfo()->willReturn(false);

        $customMailMessage = $this->prophesize(MailMessage::class);
        $customMailMessage->isEnabled()->shouldBeCalled()->willReturn(true);
        $customMailMessage->getSubject('fr')->shouldBeCalled()->willReturn('Bonjour %firstname%');
        $customMailMessage->getSubject('en')->shouldBeCalled()->willReturn('Hello %firstname%');
        $customMailMessage->getContent('fr')->shouldBeCalled()->willReturn('Votre agenda : %agenda%');
        $customMailMessage->getContent('en')->shouldBeCalled()->willReturn('Your schedule: %agenda%');

        $expectedCustomMessage = new Message(
            $this->event->reveal(),
            $this->dateTime,
            $mailType,
            true,
            false
        );
        $expectedCustomMessage->translate(
            'fr',
            'Bonjour %firstname%',
            'Votre agenda : %agenda%',
            $this->dateTime
        );
        $expectedCustomMessage->translate(
            'en',
            'Hello %firstname%',
            'Your schedule: %agenda%',
            $this->dateTime
        );

        $this->messageFactory
            ->create($this->event->reveal(), $messageId)
            ->shouldBeCalled()
            ->willReturn($defaultMessage->reveal())
        ;

        $this->messageRepository
            ->getOneByEventAndTypeAndAssociatedType(
                $this->event->reveal(),
                $mailType,
                $this->type1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($customMailMessage->reveal())
        ;

        $this->messageRepository
            ->getOneByEventAndTypeAndAssociatedType(
                $this->event->reveal(),
                $mailType,
                $this->type2->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->substitutionHandler
            ->handle(
                new PrepareBatchSheetMailView(
                    $this->event->reveal(),
                    $this->user1->reveal(),
                    $mailType,
                    'en',
                    $this->sheet1->reveal()
                ),
                $expectedCustomMessage
            )
            ->shouldBeCalled()
            ->willReturn(
                new SubstitutionResult(
                    'whatever_subject',
                    'whatever_content',
                    ['%firstname%' => 'Korben DALLAS'],
                    ['%agenda%' => 'Day one : meetings!']
                )
            )
        ;

        $this->substitutionHandler
            ->handle(
                new PrepareBatchSheetMailView(
                    $this->event->reveal(),
                    $this->user2->reveal(),
                    $mailType,
                    'fr',
                    $this->sheet2->reveal()
                ),
                $defaultMessage->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                new SubstitutionResult(
                    'whatever_subject',
                    'whatever_content',
                    ['%firstname%' => 'Leeloo EKBAT DE SEBAT'],
                    ['%agenda%' => 'Day one : full of meetings!']
                )
            )
        ;

        $this->substitutionHandler
            ->handle(
                new PrepareBatchSheetMailView(
                    $this->event->reveal(),
                    $this->user3->reveal(),
                    $mailType,
                    'fr',
                    $this->sheet3->reveal()
                ),
                $expectedCustomMessage
            )
            ->shouldBeCalled()
            ->willReturn(
                new SubstitutionResult(
                    'whatever_subject',
                    'whatever_content',
                    ['%firstname%' => 'Jean-Baptiste-Emmanuel ZORG'],
                    ['%agenda%' => 'Day one : no meeting.']
                )
            )
        ;

        $this->mailSender
            ->send(
                $expectedCustomMessage,
                [
                    'user1@example.net' => new ReceiverView(
                        'user1@example.net',
                        [
                            '%firstname%' => 'Korben DALLAS',
                            '%agenda%' => 'Day one : meetings!',
                        ],
                        'en'
                    ),
                    'user3@example.net' => new ReceiverView(
                        'user3@example.net',
                        [
                            '%firstname%' => 'Jean-Baptiste-Emmanuel ZORG',
                            '%agenda%' => 'Day one : no meeting.',
                        ],
                        'fr'
                    ),
                ]
            )
            ->shouldBeCalled()
        ;

        $this->mailSender
            ->send(
                $defaultMessage->reveal(),
                [
                    'user2@example.net' => new ReceiverView(
                        'user2@example.net',
                        [
                            '%firstname%' => 'Leeloo EKBAT DE SEBAT',
                            '%agenda%' => 'Day one : full of meetings!',
                        ],
                        'fr'
                    ),
                ]
            )
            ->shouldBeCalled()
        ;

        $this->sendEmailingByTypeHandler->handle(
            new SendEmailingByType(
                $this->event->reveal(),
                $messageId,
                [$this->sheet1->reveal(), $this->sheet2->reveal(), $this->sheet3->reveal()]
            )
        );
    }

    public function test_send_message_with_copy_to_billing_email()
    {
        $messageId = 'sheet.validated';
        $mailType = 'mail_sheet_validated';

        $defaultMessage = $this->prophesize(Message::class);
        $defaultMessage->getName()->shouldBeCalled()->willReturn($mailType);
        $defaultMessage->isSendToEmailTeam()->willReturn(false);
        $defaultMessage->isSendEmailToBillingInfo()->willReturn(true);

        $customMailMessage = $this->prophesize(MailMessage::class);
        $customMailMessage->isEnabled()->shouldBeCalled()->willReturn(true);
        $customMailMessage->getSubject('fr')->shouldBeCalled()->willReturn('Bonjour %firstname%');
        $customMailMessage->getSubject('en')->shouldBeCalled()->willReturn('Hello %firstname%');
        $customMailMessage->getContent('fr')->shouldBeCalled()->willReturn('Votre agenda : %agenda%');
        $customMailMessage->getContent('en')->shouldBeCalled()->willReturn('Your schedule: %agenda%');

        $expectedCustomMessage = new Message(
            $this->event->reveal(),
            $this->dateTime,
            $mailType,
            false,
            true
        );
        $expectedCustomMessage->translate(
            'fr',
            'Bonjour %firstname%',
            'Votre agenda : %agenda%',
            $this->dateTime
        );
        $expectedCustomMessage->translate(
            'en',
            'Hello %firstname%',
            'Your schedule: %agenda%',
            $this->dateTime
        );

        $billingInfoSheet1 = $this->prophesize(BillingInfo::class);
        $billingInfoSheet1->getSheet()->shouldBeCalled()->willReturn($this->sheet1->reveal());
        $billingInfoSheet1->getEmail()->shouldBeCalled()->willReturn('billing@example.net');

        $this->billingInfoRepository
            ->getBySheets([$this->sheet1->reveal(), $this->sheet3->reveal()])
            ->willReturn([$billingInfoSheet1->reveal()])
        ;

        $this->messageFactory
            ->create($this->event->reveal(), $messageId)
            ->shouldBeCalled()
            ->willReturn($defaultMessage->reveal())
        ;

        $this->messageRepository
            ->getOneByEventAndTypeAndAssociatedType(
                $this->event->reveal(),
                $mailType,
                $this->type1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($customMailMessage->reveal())
        ;

        $this->substitutionHandler
            ->handle(
                new PrepareBatchSheetMailView(
                    $this->event->reveal(),
                    $this->user1->reveal(),
                    $mailType,
                    'en',
                    $this->sheet1->reveal()
                ),
                $expectedCustomMessage
            )
            ->shouldBeCalled()
            ->willReturn(
                new SubstitutionResult(
                    'whatever_subject',
                    'whatever_content',
                    ['%firstname%' => 'Korben DALLAS'],
                    ['%agenda%' => 'Day one : meetings!']
                )
            )
        ;

        $this->substitutionHandler
            ->handle(
                new PrepareBatchSheetMailView(
                    $this->event->reveal(),
                    $this->user3->reveal(),
                    $mailType,
                    'fr',
                    $this->sheet3->reveal()
                ),
                $expectedCustomMessage
            )
            ->shouldBeCalled()
            ->willReturn(
                new SubstitutionResult(
                    'whatever_subject',
                    'whatever_content',
                    ['%firstname%' => 'Jean-Baptiste-Emmanuel ZORG'],
                    ['%agenda%' => 'Day one : no meeting.']
                )
            )
        ;

        $this->mailSender
            ->send(
                $expectedCustomMessage,
                [
                    'user1@example.net' => new ReceiverView(
                        'user1@example.net',
                        [
                            '%firstname%' => 'Korben DALLAS',
                            '%agenda%' => 'Day one : meetings!',
                        ],
                        'en'
                    ),
                    'billing@example.net' => new ReceiverView(
                        'billing@example.net',
                        [
                            '%firstname%' => 'Korben DALLAS',
                            '%agenda%' => 'Day one : meetings!',
                        ],
                        'en'
                    ),
                    'user3@example.net' => new ReceiverView(
                        'user3@example.net',
                        [
                            '%firstname%' => 'Jean-Baptiste-Emmanuel ZORG',
                            '%agenda%' => 'Day one : no meeting.',
                        ],
                        'fr'
                    ),
                ]
            )
            ->shouldBeCalled()
        ;

        $this->sendEmailingByTypeHandler->handle(
            new SendEmailingByType(
                $this->event->reveal(),
                $messageId,
                [$this->sheet1->reveal(), $this->sheet3->reveal()]
            )
        );
    }
}
