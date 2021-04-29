<?php

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;
use SendGrid\Mail;
use SendGrid\Response;
use Twig\Environment;
use Twig\Template;

class SendGridApiAdapterTest extends TestCase
{
    public function testSend()
    {
        $event     = EventFactory::createEvent();
        $receiver  = new ReceiverView('user@vimeet.com', [], 'fr');
        $createdAt = new \DateTime();
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $template = $this->prophesize(Template::class);
        foreach ($event->getLocales() as $locale) {
            $template->render(['mail' => new MessageContentMail($message, $event, $locale)])->shouldBeCalled()->willReturn('test content');
        }

        $twig = $this->prophesize(Environment::class);
        $twig->load($message->getTemplate())->shouldBeCalled()->willReturn($template);

        $client = $this->prophesize(SendGridApiClient::class);
        $client->send(Argument::type(Mail::class))->shouldBeCalledTimes(1)->willReturn(new Response(202));

        (new SendGridApiAdapter(
            $client->reveal(),
            $twig->reveal(),
            new EventSender('vimeet.proximum.dev', 'no-reply@vimeet.proximum.dev')
        ))->send($message, [$receiver]);
    }
}
