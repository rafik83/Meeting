<?php

namespace Proximum\Vimeet\Tests\Application\Command\Transactional\Mail;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Transactional\Mail\UpdateCustomized;
use Proximum\Vimeet\Application\Command\Transactional\Mail\UpdateCustomizedHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class UpdateCustomizedHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);
        $date = new \DateTime();
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);

        $message = new Message(
            $event->reveal(),
            Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED,
            $date,
            false,
            [
                $type1->reveal(),
                $type2->reveal()
            ]
        );
        $message->translate('fr', 'Nouveau sujet', 'Nouveau contenu');
        $message->translate('en', 'origin subject', 'origin content');

        $message->update([$type1->reveal(), $type3->reveal(),], true);
        $message->updateTranslations([
            'fr' => [
                'subject' => 'Nouveau sujet',
                'content' => 'Nouveau contenu',
            ],
                    'en' => [
                'subject' => 'origin subject',
                'content' => 'origin content',
            ],
        ]);

        $messageRepository->update($message)->shouldBeCalled();

        $command = new UpdateCustomized(
            $message,
            Constant::TRANSACTIONAL_MAIL_LIST[Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED]
        );
        $command->enabled = false;
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

        $handler = new UpdateCustomizedHandler($messageRepository->reveal());
        $handler->handle($command);
    }
}
