<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class UpdatePreviewHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime      = new \DateTime();
        $templateValue = [
            '69b3cde1' => [
                    'component' => 'object',
                    'type'      => 'image',
                    'config'    => [
                        'label'       => ['fr' => 'Image'],
                        'placeholder' => ['fr' => ''],
                        'help'        => ['fr' => ''],
                        'required'    => false,
                        'style'       => '',
                        'products'    => null,
                    ],
                ],
            '69b3cde2' => [
                'component' => 'object',
                'type'      => 'editable-text',
                'config'    => [
                    'label'        => ['fr' => 'Texte modifiable'],
                    'placeholder'  => ['fr' => ''],
                    'hideLabel'    => false,
                    'help'         => ['fr' => ''],
                    'maxLength'    => '',
                    'required'     => false,
                    'type'         => 'title',
                    'style'        => '',
                    'translatable' => false,
                    'tag'          => '',
                ],
            ],
            '69b3cde3' => [
                'component' => 'object',
                'type'      => 'editable-text',
                'config'    => [
                    'label'        => ['fr' => 'Texte modifiable'],
                    'placeholder'  => ['fr' => ''],
                    'hideLabel'    => false,
                    'help'         => ['fr' => ''],
                    'maxLength'    => '',
                    'required'     => false,
                    'type'         => 'title',
                    'style'        => '',
                    'translatable' => false,
                    'tag'          => '',
                ],
            ],
        ];

        $template     = new SheetTemplate('Toto', $templateValue, ['fr'], 'fr', $dateTime);
        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $image        = new Image('69b3cde1', 'image', [], 'fr', 'fr');
        $title        = new EditableText('69b3cde2', 'editable-text', ['foobar' => 'foobar'], 'fr', 'fr');
        $description  = new EditableText('69b3cde3', 'editable-text', ['foobar' => 'foobar'], 'fr', 'fr');

        $templateData->addChild(0, '69b3cde1', $image);
        $templateData->addChild(0, '69b3cde2', $title);
        $templateData->addChild(0, '69b3cde3', $description);

        $expectedTemplate = new SheetTemplate('Toto', $templateValue, ['fr'], 'fr', $dateTime);
        $expectedTemplate->setPreview(['69b3cde1', '69b3cde2', '69b3cde3']);

        // Command
        $command                 = new UpdatePreview($template, [$image, $title, $description]);
        $command->previewObjects = ['69b3cde1', '69b3cde2', '69b3cde3'];

        // Mock
        $sheetRepository     = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        $templateDataFactory->createFromTemplate($template)->shouldBeCalled()->willReturn($templateData);

        $sheetRepository->set($expectedTemplate)->shouldBeCalled();

        // Handler
        $handler = new UpdatePreviewHandler($sheetRepository->reveal(), $templateDataFactory->reveal());
        $handler->handle($command);
    }
}
