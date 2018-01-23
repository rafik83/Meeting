<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\Process;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ProcessHandler;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Messaging\SubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\SendGridApiAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;
use PHPUnit\Framework\TestCase;

class ProcessHandlerTest extends TestCase
{
    public function testSend()
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $receiver  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet     = SheetFactory::create($event, $receiver);
        $message   = new Message($event, $createdAt, 'test', 'test subject', 'test content');

        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);
        $campaign->setMessage($message);
        $campaign->addRecipient(Campaign::RECIPIENT_PARTICIPANTS);
        $campaign->addSheet($sheet);

        $template = $this->prophesize(\Twig_TemplateInterface::class);
        foreach ($event->getLocales() as $locale) {
            $template->render(['mail' => new MessageContentMail($message, $event, $locale)])->willReturn('test content ' . $locale);
        }

        $twig = $this->prophesize(\Twig_Environment::class);
        $twig->load($message->getTemplate())->willReturn($template);

        $mailer                = new SendGridApiAdapter(
            $this->prophesize(SendGridApiClient::class)->reveal(),
            $twig->reveal(),
            $this->getEventSender()
        );

        $handler = new ProcessHandler(
            $this->prophesize(BillingInfoRepositoryInterface::class)->reveal(),
            $this->prophesize(CampaignRepositoryInterface::class)->reveal(),
            $mailer,
            $this->prophesize(SubstitutionsProvider::class)->reveal()
        );

        $handler->handle(new Process($campaign));
        $this->assertInstanceOf(\DateTimeInterface::class, $campaign->getProcessedAt());
    }

    /**
     * @expectedException        \Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException
     * @expectedExceptionMessage flash.messaging.campaign.send.failure.no_sheet
     */
    public function testSendThrowsExceptionIfCampaignHasNoSheet()
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);
        $mailer    = new SendGridApiAdapter(
            $this->prophesize(SendGridApiClient::class)->reveal(),
            $this->prophesize(\Twig_Environment::class)->reveal(),
            $this->getEventSender()
        );

        $handler = new ProcessHandler(
            $this->prophesize(BillingInfoRepositoryInterface::class)->reveal(),
            $this->prophesize(CampaignRepositoryInterface::class)->reveal(),
            $mailer,
            $this->prophesize(SubstitutionsProvider::class)->reveal()
        );

        $handler->handle(new Process($campaign));
    }

    /**
     * @expectedException        \Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException
     * @expectedExceptionMessage flash.messaging.campaign.send.failure.no_message
     */
    public function testSendThrowsExceptionIfCampaignHasNoMessage()
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);
        $campaign->addSheet(SheetFactory::create($event));

        $mailer = new SendGridApiAdapter(
            $this->prophesize(SendGridApiClient::class)->reveal(),
            $this->prophesize(\Twig_Environment::class)->reveal(),
            $this->getEventSender()
        );

        $handler = new ProcessHandler(
            $this->prophesize(BillingInfoRepositoryInterface::class)->reveal(),
            $this->prophesize(CampaignRepositoryInterface::class)->reveal(),
            $mailer,
            $this->prophesize(SubstitutionsProvider::class)->reveal()
        );

        $handler->handle(new Process($campaign));
    }

    /**
     * @expectedException        \Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException
     * @expectedExceptionMessage flash.messaging.campaign.send.failure.no_recipient
     */
    public function testSendThrowsExceptionIfCampaignHasNoRecipient()
    {
        $event     = EventFactory::createEvent();
        $createdAt = new \DateTime();
        $campaign  = new Campaign($event, 'amazing campaign', [], $createdAt);
        $campaign->addSheet(SheetFactory::create($event));
        $campaign->setMessage(new Message($event, $createdAt, 'test', 'test subject', 'test content'));

        $mailer = new SendGridApiAdapter(
            $this->prophesize(SendGridApiClient::class)->reveal(),
            $this->prophesize(\Twig_Environment::class)->reveal(),
            $this->getEventSender()
        );

        $handler = new ProcessHandler(
            $this->prophesize(BillingInfoRepositoryInterface::class)->reveal(),
            $this->prophesize(CampaignRepositoryInterface::class)->reveal(),
            $mailer,
            $this->prophesize(SubstitutionsProvider::class)->reveal()
        );

        $handler->handle(new Process($campaign));
    }

    private function getEventSender()
    {
        return new EventSender('vimeet.proximum.dev', 'no-reply@vimeet.proximum.dev');
    }
}
