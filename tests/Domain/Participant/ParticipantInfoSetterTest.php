<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\ParticipantInfoSetter;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Telephone;

class ParticipantInfoSetterTest extends TestCase
{
    public function testSetMobile()
    {
        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $phoneObject = new Telephone(
            'phone', 'telephone', ['tags' => ['participant_mobile', 'participant_data']], 'fr', 'fr'
        );
        $phoneObject->setTelephone('+33 666 999 000');
        $block->addChild(1, '3ad4b72f', $phoneObject);
        $templateData->addChild(0, '67019e4a', $block);

        $participant = $this->prophesize(Participant::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantInfoSetter = new ParticipantInfoSetter(
            $templateDataFactory->reveal(),
            $participantRepository->reveal()
        );

        $templateDataFactory->createProfileTemplate($participant->reveal(), 'fr')->willReturn($templateData);
        $phoneObject->setTelephone('+3311223344');
        $participant->setData($templateData->getData())->shouldBeCalled();

        $participantRepository->set($participant->reveal())->shouldBeCalled();
        $participantInfoSetter->setMobile($participant->reveal(), '+3311223344', 'fr');
    }

    public function testNotFoundMobileTaggedObject()
    {
        $templateData = new TemplateData('root', [], 'fr', 'fr');

        $participant = $this->prophesize(Participant::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantInfoSetter = new ParticipantInfoSetter(
            $templateDataFactory->reveal(),
            $participantRepository->reveal()
        );

        $templateDataFactory->createProfileTemplate($participant->reveal(), 'fr')->willReturn($templateData);
        $participant->setData($templateData->getData())->shouldNotBeCalled();

        $participantRepository->set($participant->reveal())->shouldBeCalled();
        $participantInfoSetter->setMobile($participant->reveal(), '+3311223344', 'fr');
    }
}
