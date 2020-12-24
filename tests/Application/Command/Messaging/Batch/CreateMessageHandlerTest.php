<?php

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Batch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessage;
use Proximum\Vimeet\Application\Command\Messaging\Batch\CreateMessageHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;

class CreateMessageHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);
        $dateTime = new \DateTime();

        $createMessage = new CreateMessage($event->reveal(), 'name', 'subject', 'template', false, false);

        // Mock
        $twig = $this->prophesize(\Twig_Environment::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->trans('subject', [], 'mail', 'fr')->shouldBeCalled()->willReturn('subject fr');
        $translator->trans('subject', [], 'mail', 'en')->shouldBeCalled()->willReturn('subject en');

        $twig->load('template')->shouldBeCalled()->willReturn($twig);
        $twig
            ->render(Argument::that(function ($input) {
                return isset($input['mail'])
                && $input['mail'] instanceof MessageContentMail
                && 'fr' === $input['mail']->getLocale();
            }))
            ->shouldBeCalled()
            ->willReturn('<html><body>content fr</body></html>')
        ;
        $twig
            ->render(Argument::that(function ($input) {
                return isset($input['mail'])
                && $input['mail'] instanceof MessageContentMail
                && 'en' === $input['mail']->getLocale();
            }))
            ->shouldBeCalled()
            ->willReturn('<html><body>content en</body></html>')
        ;

        $handler = new CreateMessageHandler($twig->reveal(), $translator->reveal(), $dateTime);
        $result = $handler->handle($createMessage);

        $expectedWithTranslation = new Message(
            $event->reveal(),
            $dateTime,
            'name',
            false,
            false
        );

        $expectedWithTranslation->translate('fr', 'subject fr', '<html><body>content fr</body></html>', $dateTime);
        $expectedWithTranslation->translate('en', 'subject en', '<html><body>content en</body></html>', $dateTime);

        $this->assertEquals($expectedWithTranslation, $result);
    }
}
