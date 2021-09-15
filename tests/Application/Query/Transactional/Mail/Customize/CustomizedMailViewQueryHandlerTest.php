<?php

namespace Proximum\Vimeet\Tests\Application\Query\Transactional\Mail\Customize;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Customize\CustomizedMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Customize\CustomizedMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\Customize\CustomizedMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class CustomizedMailViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $type = Constant::TRANSACTIONAL_MAIL_KEY_SHEET_REFUSED;
        $date = new \DateTime();

        $type15 = $this->prophesize(Type::class);
        $type16 = $this->prophesize(Type::class);

        $type15->getId()->shouldBeCalled()->willReturn(15);
        $type15->getTitle('fr')->shouldBeCalled()->willReturn('type15');

        $type16->getId()->shouldBeCalled()->willReturn(16);
        $type16->getTitle('fr')->shouldBeCalled()->willReturn('type16');

        $message = new Message(
            $event->reveal(),
            $type,
            $date,
            true,
            [
                $type15->reveal(),
                $type16->reveal(),
            ]
        );
        $message->translate('fr', 'Subject in french', 'content in french');

        $reflectionMessage = new \ReflectionClass(Message::class);
        $property = $reflectionMessage->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($message, 12);
        $property->setAccessible(false);

        $query = new CustomizedMailViewQuery(
            $message,
            'fr'
        );
        $handler = new CustomizedMailViewQueryHandler();


        $result = $handler->handle($query);

        $expected = new CustomizedMailView(
            12,
            $type,
            'Subject in french',
            true,
            true,
            [
                15 => 'type15',
                16 => 'type16',
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
