<?php

namespace Proximum\Vimeet\Tests\Domain\Rule;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Rule\TagIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TagIdentifierTest extends TestCase
{
    public function testIdentifyType()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();
        $registrationTemplate = new RegistrationTemplate('title', [], ['fr', 'en'], 'fr', $date);
        $type->setRegistrationTemplate($registrationTemplate);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $title        = new EditableText('69b3cde2', 'editable-text', ['tags' => ['foobar']], 'fr', 'fr');
        $description  = new EditableText('69b3cde3', 'editable-text', ['tags' => ['foobar', 'participant_position']], 'fr', 'fr');

        $templateData->addChild(0, '69b3cde2', $title);
        $templateData->addChild(0, '69b3cde3', $description);

        // Mock
        $templateFactory = $this->prophesize(TemplateDataFactory::class);
        $templateFactory->createFromTemplate($registrationTemplate, [])->shouldBeCalled()->willReturn($templateData);

        // Test
        $tagIdentifier = new TagIdentifier($templateFactory->reveal());
        $result = $tagIdentifier->identify($type);

        $expected = [
            0 => 'foobar',
            2 => 'participant_position',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testIdentifyCategory()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $type2 = new Type($event);
        $date  = new \DateTime();
        $reflection = new \ReflectionClass(RegistrationTemplate::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $registrationTemplate = new RegistrationTemplate('title', [], ['fr', 'en'], 'fr', $date);
        $property->setValue($registrationTemplate, 1);
        $registrationTemplate2 = new RegistrationTemplate('title', [], ['fr', 'en'], 'fr', $date);
        $property->setValue($registrationTemplate2, 2);
        $property->setAccessible(false);

        $type->setRegistrationTemplate($registrationTemplate);
        $type2->setRegistrationTemplate($registrationTemplate2);

        $category = new Category($event);
        $category->addType($type);
        $category->addType($type2);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $title        = new EditableText('69b3cde2', 'editable-text', ['tags' => ['foobar']], 'fr', 'fr');
        $description  = new EditableText('69b3cde3', 'editable-text', ['tags' => ['foobar', 'participant_position']], 'fr', 'fr');

        $templateData->addChild(0, '69b3cde2', $title);
        $templateData->addChild(0, '69b3cde3', $description);

        $templateData2 = new TemplateData('root', [], 'fr', 'fr');
        $title2        = new EditableText('69b3cde2', 'editable-text', ['tags' => ['toto', 'truc']], 'fr', 'fr');
        $description2  = new EditableText('69b3cde3', 'editable-text', ['tags' => ['truc', 'bidule']], 'fr', 'fr');

        $templateData2->addChild(0, '69b3cde2', $title2);
        $templateData2->addChild(0, '69b3cde3', $description2);

        // Mock
        $templateFactory = $this->prophesize(TemplateDataFactory::class);
        $templateFactory->createFromTemplate($registrationTemplate, [])->shouldBeCalled()->willReturn($templateData);
        $templateFactory->createFromTemplate($registrationTemplate2, [])->shouldBeCalled()->willReturn($templateData2);

        // Test
        $tagIdentifier = new TagIdentifier($templateFactory->reveal());
        $result        = $tagIdentifier->identify($category);

        $expected = [
            0 => 'foobar',
            1 => 'participant_position',
            2 => 'toto',
            3 => 'truc',
            4 => 'bidule',
        ];

        $this->assertEquals($expected, $result);
    }
}
