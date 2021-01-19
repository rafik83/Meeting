<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\UpdateCompany;
use Proximum\Vimeet\Application\Command\Participant\UpdateCompanyHandler;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateCompanyHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $template = [
            '67019e4a' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [
                    'style' => 'style-1',
                ],
                'children'  => [
                    '3ad4b72f' => [
                        'component' => 'object',
                        'type'      => 'editable-text',
                        'config'    => [
                            'style'        => 'style-1',
                            'label'        => [
                                'fr' => 'Nom (Société / Organisme)',
                                'en' => 'Company name',
                            ],
                            'placeholder'  => [
                                'fr' => 'Nom (Société / Organisme)',
                                'en' => 'Company name',
                            ],
                            'help'         => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'       => 250,
                            'required'     => true,
                            'type'         => 'text',
                            'translatable' => false,
                            'tags'         => ['sheet_organization', 'sheet_title', 'sheet_data'],
                        ],
                    ],
                    '9ef18c06' => [
                        'component' => 'object',
                        'type'      => 'url',
                        'config'    => [
                            'style'        => 'style-1',
                            'label'        => [
                                'fr' => 'Site internet',
                                'en' => 'Website',
                            ],
                            'placeholder'  => [
                                'fr' => 'Site internet',
                                'en' => 'Website',
                            ],
                            'help'         => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'       => '',
                            'required'     => false,
                            'type'         => 'text',
                            'translatable' => false,
                            'tags'         => ['participant_website', 'sheet_data'],
                        ],
                    ],
                    '93e093f'  => [
                        'component' => 'object',
                        'type'      => 'editable-text',
                        'config'    => [
                            'style'        => 'style-1',
                            'label'        => [
                                'fr' => 'Adresse',
                                'en' => 'Address',
                            ],
                            'placeholder'  => [
                                'fr' => 'Adresse',
                                'en' => 'Address',
                            ],
                            'help'         => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'       => '',
                            'required'     => true,
                            'type'         => 'text',
                            'translatable' => false,
                            'tags'         => ['participant_address', 'sheet_data'],
                        ],
                    ],
                    'de66af5d' => [
                        'component' => 'object',
                        'type'      => 'editable-text',
                        'config'    => [
                            'style'        => 'style-1',
                            'label'        => [
                                'fr' => 'Code postal',
                                'en' => 'Zip code',
                            ],
                            'placeholder'  => [
                                'fr' => 'Code postal',
                                'en' => 'Zip code',
                            ],
                            'help'         => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'       => '',
                            'required'     => true,
                            'type'         => 'text',
                            'translatable' => false,
                            'tags'         => ['participant_zipcode', 'sheet_data'],
                        ],
                    ],
                    'd224f0e7' => [
                        'component' => 'object',
                        'type'      => 'editable-text',
                        'config'    => [
                            'style'        => 'style-1',
                            'label'        => [
                                'fr' => 'Ville',
                                'en' => 'City',
                            ],
                            'placeholder'  => [
                                'fr' => 'Ville',
                                'en' => 'City',
                            ],
                            'help'         => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'       => '',
                            'required'     => true,
                            'type'         => 'text',
                            'translatable' => false,
                            'tags'         => ['participant_city', 'sheet_data'],
                        ],
                    ],
                    'e801edd4' => [
                        'component' => 'object',
                        'type'      => 'country',
                        'config'    => [
                            'style'       => 'style-1',
                            'label'       => [
                                'fr' => 'Pays',
                                'en' => 'Country',
                            ],
                            'placeholder' => [
                                'fr' => 'Pays',
                                'en' => 'Country',
                            ],
                            'help'        => [
                                'fr' => '',
                                'en' => '',
                            ],
                            'length'      => '',
                            'required'    => true,
                            'type'        => 'text',
                            'tags'        => ['participant_country', 'sheet_data'],
                        ],
                    ],
                ],
            ],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $sheet = new Sheet($event, $type, [], $user, $now);
        $sheet->setRegistrationData([
            '3ad4b72f' => ['text' => 'oldFoo'],
            '9ef18c06' => ['url' => 'http://www.oldfoo.com'],
            '93e093f'  => ['text' => '10 rue de la oldFoo'],
            'de66af5d' => ['text' => '75002'],
            'd224f0e7' => ['text' => 'oldFooVille'],
            'e801edd4' => ['country' => 'EN'],
        ]);
        $participant = new Participant(
            $sheet,
            $user,
            [],
            true,
            $now
        );

        // Mock
        $sheetRepository     = $this->prophesize(SheetRepositoryInterface::class);
        $accountSynchronizer = $this->prophesize(Synchronizer::class);
        $eventDispatcher     = $this->prophesize(DelayedEventDispatcher::class);
        $uploadFileHandler   = $this->prophesize(UploadFileHandler::class);

        $expectedSheet = new Sheet($event, $type, [], $user, $now);
        $expectedSheet->setTitle('foo');
        $expectedSheet->setRegistrationData([
            '3ad4b72f' => ['text' => 'foo'],
            '9ef18c06' => ['url' => 'http://www.foo.com'],
            '93e093f'  => ['text' => '10 rue de la Foo'],
            'de66af5d' => ['text' => '75001'],
            'd224f0e7' => ['text' => 'FooVille'],
            'e801edd4' => ['country' => 'FR'],
        ]);

        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, Argument::type(SheetUpdatedEvent::class))->shouldBeCalled();

        $handler = new UpdateCompanyHandler(
            $sheetRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal(),
            $uploadFileHandler->reveal()
        );

        $templateData  = new TemplateData('root', [], 'fr', 'fr');
        $block         = new Block('12', [], 'fr', 'fr');
        $objectCompany = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['sheet_organization', 'sheet_title', 'sheet_data'],
        ], 'fr', 'fr');
        $objectCompany->setContentValue('oldFoo');
        $objectUrl = new TemplateObject\Url('69b3cde1', 'url', [
            'tags' => ['participant_website', 'sheet_data'],
        ], 'fr', 'fr');
        $objectUrl->setContentValue('http://www.oldfoo.com');
        $objectAddress = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_address', 'sheet_data'],
        ], 'fr', 'fr');
        $objectAddress->setContentValue('10 rue de la oldFoo');
        $objectZipCode = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_zipcode', 'sheet_data'],
        ], 'fr', 'fr');
        $objectZipCode->setContentValue('75002');
        $objectCity = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_city', 'sheet_data'],
        ], 'fr', 'fr');
        $objectCity->setContentValue('oldFooVille');
        $objectCountry = new TemplateObject\Country('69b3cde1', 'country', [
            'tags' => ['participant_country', 'sheet_data'],
        ], 'fr', 'fr');
        $objectCountry->setContentValue('EN');

        $block->addChild(1, '3ad4b72f', $objectCompany);
        $block->addChild(1, '9ef18c06', $objectUrl);
        $block->addChild(1, '93e093f', $objectAddress);
        $block->addChild(1, 'de66af5d', $objectZipCode);
        $block->addChild(1, 'd224f0e7', $objectCity);
        $block->addChild(1, 'e801edd4', $objectCountry);
        $templateData->addChild(0, '67019e4a', $block);

        // Expected
        $expectedTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $expectedBlock        = new Block('12', [], 'fr', 'fr');

        $exObjectCompany = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['sheet_organization', 'sheet_title', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectCompany->setContentValue('foo');
        $exObjectUrl = new TemplateObject\Url('69b3cde1', 'url', [
            'tags' => ['participant_website', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectUrl->setContentValue('http://www.foo.com');
        $exObjectAddress = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_address', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectAddress->setContentValue('10 rue de la Foo');
        $exObjectZipCode = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_zipcode', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectZipCode->setContentValue('75001');
        $exObjectCity = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_city', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectCity->setContentValue('FooVille');
        $exObjectCountry = new TemplateObject\Country('69b3cde1', 'country', [
            'tags' => ['participant_country', 'sheet_data'],
        ], 'fr', 'fr');
        $exObjectCountry->setContentValue('FR');

        $expectedBlock->addChild(1, '3ad4b72f', $exObjectCompany);
        $expectedBlock->addChild(1, '9ef18c06', $exObjectUrl);
        $expectedBlock->addChild(1, '93e093f', $exObjectAddress);
        $expectedBlock->addChild(1, 'de66af5d', $exObjectZipCode);
        $expectedBlock->addChild(1, 'd224f0e7', $exObjectCity);
        $expectedBlock->addChild(1, 'e801edd4', $exObjectCountry);
        $expectedTemplateData->addChild(0, '67019e4a', $expectedBlock);

        $accountSynchronizer->set($expectedTemplateData, $user)->shouldBeCalled();

        $handler->handle(
            new UpdateCompany(
                $templateData,
                $sheet,
                $participant,
                'fr',
                [
                    '3ad4b72f' => ['text' => 'foo'],
                    '9ef18c06' => ['url' => 'http://www.foo.com'],
                    '93e093f'  => ['text' => '10 rue de la Foo'],
                    'de66af5d' => ['text' => '75001'],
                    'd224f0e7' => ['text' => 'FooVille'],
                    'e801edd4' => ['country' => 'FR'],
                ],
                $user
            )
        );
    }
}
