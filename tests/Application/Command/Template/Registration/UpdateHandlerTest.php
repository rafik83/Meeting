<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Update;
use Proximum\Vimeet\Application\Command\Template\Registration\UpdateHandler;
use Proximum\Vimeet\Application\Components\Registration\RegistrationTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        // context
        $dateTime = new \DateTime();
        $array    = [
            '811f6edf' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [
                    'style' => 'style-1',
                ],
                'children'  => [
                    'dded0597' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'     =>[
                            'content' => [
                                'fr' => 'Profil',
                            ],
                            'type' => 'titre',
                        ],
                    ],
                ],
            ],
        ];
        $registrationTemplate = new RegistrationTemplate('base tata', [], ['fr'], 'fr', $dateTime);
        $update               = new Update($registrationTemplate);
        $update->title        = 'base toto';
        $update->value        = $array;

        // expected
        $expectedTemplate = new RegistrationTemplate('base toto', $array, ['fr'], 'fr', $dateTime);

        // mock
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $registrationTemplateRepository->set($expectedTemplate)->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $templateData = $this->prophesize(TemplateData::class);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createRegistrationFromTemplate(
                $registrationTemplate,
                'fr'
            )
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $registrationTemplateValidatorTranslated = $this->prophesize(RegistrationTemplateValidatorTranslated::class);
        $registrationTemplateValidatorTranslated->validate($templateData->reveal());

        $handler = new UpdateHandler(
            $registrationTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $registrationTemplateValidatorTranslated->reveal(),
            $eventDispatcher->reveal(),
            $jobQueue->reveal()
        );
        $handler->handle($update);
    }

    public function testHandleWithEvent()
    {
        $event = EventFactory::createEvent();

        // context
        $dateTime = new \DateTime();
        $array    = [
            '811f6edf' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [
                    'style' => 'style-1',
                ],
                'children'  => [
                    'dded0597' => [
                        'component' => 'object',
                        'type'      => 'text',
                        'config'     =>[
                            'content' => [
                                'fr' => 'Profil',
                            ],
                            'type' => 'titre',
                        ],
                    ],
                ],
            ],
        ];
        $registrationTemplate = new RegistrationTemplate('base tata', [], ['fr'], 'fr', $dateTime);
        $registrationTemplate->setEvent($event);
        $update               = new Update($registrationTemplate);
        $update->title        = 'base toto';
        $update->value        = $array;

        // expected
        $expectedTemplate = new RegistrationTemplate('base toto', $array, ['fr'], 'fr', $dateTime);
        $expectedTemplate->setEvent($event);

        // mock
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $registrationTemplateRepository->set($expectedTemplate)->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $registrationTemplateUpdated = new RegistrationTemplateUpdatedEvent($event);
        $eventDispatcher
            ->dispatch(Events::REGISTRATION_TEMPLATE_UPDATED, $registrationTemplateUpdated)
            ->shouldBeCalled();
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $templateData = $this->prophesize(TemplateData::class);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createRegistrationFromTemplate($registrationTemplate, 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $registrationTemplateValidatorTranslated = $this->prophesize(RegistrationTemplateValidatorTranslated::class);
        $registrationTemplateValidatorTranslated->validate($templateData->reveal());

        $handler = new UpdateHandler(
            $registrationTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $registrationTemplateValidatorTranslated->reveal(),
            $eventDispatcher->reveal(),
            $jobQueue->reveal()
        );
        $handler->handle($update);
    }
}
