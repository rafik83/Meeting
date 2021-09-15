<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\UpdateAvatar;
use Proximum\Vimeet\Application\Command\Participant\UpdateAvatarHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateAvatarHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $template = [
            '811f6edf' => [
                    'component' => 'block',
                    'type'      => '12',
                    'config'    => [
                            'style' => 'style-1',
                        ],
                    'children'  => [
                            [
                                '541f84d4' => [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    => [
                                                'style'        => 'style-1',
                                                'label'        => [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'placeholder'  => [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'help'         => [
                                                        'en' => null,
                                                        'fr' => 'Merci de donner votre prénom',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ['participant_firstname', 'participant_data'],
                                            ],
                                    ],
                                'cb66008e' => [
                                    'component' => 'object',
                                    'type'      => 'image',
                                    'config'    => [
                                        'style' => 'style-1',
                                        'label' => [
                                            'fr' => 'Photo',
                                        ],
                                        'placeholder' => [
                                            'fr' => 'Photo',
                                        ],
                                        'help' => [
                                            'fr' => '',
                                        ],
                                        'required' => false,
                                        'tags'     => ['participant_avatar', 'participant_data'],
                                    ],
                                ],
                            ],
                        ],
                ],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $date = new \DateTime();
        $sheet       = new Sheet($event, $type, [], $user, $now);
        $participant = new Participant(
            $sheet,
            $user,
            [
                '541f84d4' => ['text' => 'truc'],
                'cb66008e' => ['image' => ''],
            ],
            $owner = true,
            $date
        );

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $accountSynchronizer   = $this->prophesize(Synchronizer::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $sheetWithParticipant = new Sheet($event, $type, [], $user, $now);
        $expectedParticipant  = new Participant(
            $sheetWithParticipant,
            $user,
            [
                '541f84d4' => ['text' => 'foo'],
                'cb66008e' => ['image' => 'path/to/file'],
            ],
            $owner = true,
            $date
        );

        $participantRepository->set($expectedParticipant)->shouldBeCalled();
        $eventDispatcher->dispatch(Events::SHEET_UPDATED, Argument::that(
            function (SheetUpdatedEvent $sheetUpdatedEvent) {
                return true;
            }
        ))->shouldBeCalled();

        $handler = new UpdateAvatarHandler(
            $participantRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal()
        );

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText1->setContentValue('foo');
        $image = new TemplateObject\Image('69b3cde1', 'image', [
            'tags' => ['participant_avatar', 'participant_data'],
        ], 'fr', 'fr');
        $image->setContentValue('path/to/file');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, 'cb66008e', $image);
        $templateData->addChild(0, '811f6edf', $block);

        $accountSynchronizer->set($templateData, $user)->shouldBeCalled();

        $handler->handle(
            new UpdateAvatar(
                $templateData,
                $participant,
                'fr',
                $user
            )
        );
    }

    public function testWithoutSynchronizer()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2 = new User('test2@test.com', 'salt2', 'password2', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $template = [
            '811f6edf' => [
                    'component' => 'block',
                    'type'      => '12',
                    'config'    => [
                            'style' => 'style-1',
                        ],
                    'children'  => [
                            [
                                '541f84d4' => [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    => [
                                                'style'        => 'style-1',
                                                'label'        => [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'placeholder'  => [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'help'         => [
                                                        'en' => null,
                                                        'fr' => 'Merci de donner votre prénom',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ['participant_firstname', 'participant_data'],
                                            ],
                                    ],
                                'cb66008e' => [
                                    'component' => 'object',
                                    'type'      => 'image',
                                    'config'    => [
                                        'style' => 'style-1',
                                        'label' => [
                                            'fr' => 'Photo',
                                        ],
                                        'placeholder' => [
                                            'fr' => 'Photo',
                                        ],
                                        'help' => [
                                            'fr' => '',
                                        ],
                                        'required' => false,
                                        'tags'     => ['participant_avatar', 'participant_data'],
                                    ],
                                ],
                            ],
                        ],
                ],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $date = new \DateTime();
        $sheet = new Sheet($event, $type, [], $user, $now);
        $participant  = new Participant(
            $sheet,
            $user,
            [
                '541f84d4' => ['text' => 'truc'],
                'cb66008e' => ['image' => ''],
            ],
            $owner = true,
            $date
        );

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $accountSynchronizer   = $this->prophesize(Synchronizer::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $sheetWithParticipant = new Sheet($event, $type, [], $user, $now);
        $expectedParticipant  = new Participant(
            $sheetWithParticipant,
            $user,
            [
                '541f84d4' => ['text' => 'foo'],
                'cb66008e' => ['image' => 'path/to/file'],
            ],
            $owner = true,
            $date
        );

        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, Argument::that(
            function (SheetUpdatedEvent $sheetUpdatedEvent) {
                return true;
            }
        ))->shouldBeCalled();

        $handler = new UpdateAvatarHandler(
            $participantRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal()
        );

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText1->setContentValue('foo');
        $image = new TemplateObject\Image('69b3cde1', 'image', [
            'tags' => ['participant_avatar', 'participant_data'],
        ], 'fr', 'fr');
        $image->setContentValue('path/to/file');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, 'cb66008e', $image);
        $templateData->addChild(0, '811f6edf', $block);

        $accountSynchronizer->set($templateData, $user)->shouldNotBeCalled();

        $handler->handle(
            new UpdateAvatar(
                $templateData,
                $participant,
                'fr',
                $user2
            )
        );
    }
}
