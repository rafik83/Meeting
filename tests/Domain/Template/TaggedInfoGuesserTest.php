<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Telephone;

class TaggedInfoGuesserTest extends TestCase
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var \DateTime */
    private $dateTime;

    /** @var RegistrationTemplate */
    private $registrationTemplate;

    /** @var Telephone */
    private $phoneObject;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getFallback()->willReturn('fr');
        $this->templateDataFactory  = $this->prophesize(TemplateDataFactory::class);
        $this->dateTime             = new \DateTime('2017-01-01 10:00:00');
        $this->registrationTemplate = new RegistrationTemplate('base tata', [], ['fr'], 'fr', $this->dateTime, $this->event->reveal());
        $this->phoneObject          = new Telephone('phone', 'telephone', ['tags' => ['participant_mobile']], 'fr', 'fr');
    }

    public function testGuess()
    {
        // Template data
        $this->phoneObject->setContentValue('060606060');
        $block = new Block(12, [], 'fr', 'fr');
        $block->addChild(0, 'barfoo', $this->phoneObject);
        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $templateData->addChild(0, 'foobar', $block);

        // Mock
        $this->templateDataFactory
            ->create([], [], 'fr', 'fr', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $guesser = new TaggedInfoGuesser($this->templateDataFactory->reveal());

        $this->assertEquals(['060606060'], $guesser->guess($this->registrationTemplate, [], 'participant_mobile', 'fr'));
    }

    public function testGuessFirst()
    {
        $tag    = 'participant_mobile';
        $locale = 'fr';

        // Template data
        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block        = new Block(12, [], 'fr', 'fr');

        $block->addChild(0, 'a34e56d', $this->phoneObject);
        $templateData->addChild(0, 'a34e56d', $block);

        $this->phoneObject->setContentValue('0123456789');

        // Mock
        $this->templateDataFactory
            ->createFromTemplate($this->registrationTemplate, [], 'fr', 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $guesser = new TaggedInfoGuesser($this->templateDataFactory->reveal());

        $expected = '0123456789';

        $this->assertEquals($expected, $guesser->guessFirst($this->registrationTemplate, [], $tag, $locale));
    }

    public function testGuessFirstFromTemplateData()
    {
        $tag          = 'participant_mobile';
        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block        = new Block('12', [], 'fr', 'fr');

        $block->addChild(0, 'a34e56d', $this->phoneObject);
        $templateData->addChild(0, 'a34e56d', $block);

        $this->phoneObject->setContentValue('0089897');

        $taggedData = '0089897';

        $guesser = new TaggedInfoGuesser($this->templateDataFactory->reveal());

        $this->assertEquals($taggedData, $guesser->guessFirstFromTemplateData($templateData, $tag));
    }
}
