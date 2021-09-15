<?php

namespace Proximum\Vimeet\tests\Application\Command\Messaging\Message;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Messaging\Message\Create;
use Proximum\Vimeet\Application\Command\Messaging\Message\CreateHandler;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event            = EventFactory::createEvent();
        $command          = new Create($event);
        $command->name    = 'message_name';
        $date             = new \DateTime();

        $message = new Message($event, $date, 'message_name');

        foreach ($event->getLocales() as $locale) {
            $command->translations[$locale] = [
                'subject' => 'subject_' . $locale,
                'content' => 'content_' . $locale,
                'locale'  => $locale,
            ];
            $message->translate($locale, 'subject_' . $locale, 'content_' . $locale, $date);
        }

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($message)->shouldBeCalled();

        (new CreateHandler($messageRepository->reveal(), $date))->handle($command);
    }
}
