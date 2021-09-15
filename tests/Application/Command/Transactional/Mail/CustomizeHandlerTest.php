<?php

namespace Proximum\Vimeet\Tests\Application\Command\Transactional\Mail;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Transactional\Mail\Customize;
use Proximum\Vimeet\Application\Command\Transactional\Mail\CustomizeHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class CustomizeHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $date = new \DateTime();
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);

        $message = new Message(
            $event->reveal(),
            Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED,
            $date,
            true,
            [
                $type1->reveal(),
                $type2->reveal()
            ]
        );
        $message->translate('fr', 'Nouveau sujet', 'Nouveau contenu');
        $message->translate('en', 'origin subject', 'origin content');
        $messageRepository->add($message)->shouldBeCalled();

        $command = new Customize(
            $event->reveal(),
            Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED,
            Constant::TRANSACTIONAL_MAIL_LIST[Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED],
            [
                'fr' => [
                    'subject' => 'Sujet d\'origine',
                    'content' => 'Contenu d\'origine',
                ],
                'en' => [
                    'subject' => 'origin subject',
                    'content' => 'origin content',
                ],
            ]
        );
        $command->enabled = true;
        $command->associatedTypes = [
            $type1->reveal(),
            $type2->reveal(),
        ];
        $command->translations = [
            'fr' => [
                'subject' => 'Nouveau sujet',
                'content' => 'Nouveau contenu',
            ],
            'en' => [
                'subject' => 'origin subject',
                'content' => 'origin content',
            ],
        ];

        $handler = new CustomizeHandler($messageRepository->reveal(), $date);
        $handler->handle($command);
    }
}
