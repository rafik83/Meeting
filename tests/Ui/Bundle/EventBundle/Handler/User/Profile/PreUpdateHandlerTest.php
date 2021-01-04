<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\User\Profile;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdateHandler;

class PreUpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user          = UserFactory::create();
        $event         = EventFactory::createEvent();
        $sheet         = SheetFactory::create($event);
        $participant   = ParticipantFactory::create($sheet, $user);
        $locale        = 'fr';
        $currentMobile = '010203040506';

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $data         = ['69b3cde1' => ['telephone' => $currentMobile]];

        $block         = new Block('12', [], 'fr', 'fr');
        $editableText1 = new EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $block->addChild(1, '541f84d4', $editableText1);
        $templateData->addChild(1, '811f6edf', $block);

        // Mock
        $validateMobileProcessAccessChecker = $this->prophesize(ValidateMobileProcessAccessChecker::class);
        $participantInfoGuesser             = $this->prophesize(ParticipantInfoGuesser::class);

        $validateMobileProcessAccessChecker->allowToAccess($event, $user, $locale)
            ->shouldBeCalled()->willReturn(true);

        $participantInfoGuesser
            ->guessParticipantMobile($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('01000000000');

        $query = new PreUpdate($user, $participant, $event, $data, $templateData, $locale);

        $handler = new PreUpdateHandler(
            $validateMobileProcessAccessChecker->reveal(),
            $participantInfoGuesser->reveal()
        );

        $preUpdateView = $handler->handle($query);

        $this->assertEquals(PreUpdateHandler::MOBILE_VALIDATION_NEEDED, $preUpdateView->preUpdateState);
        $this->assertEquals('010203040506', $preUpdateView->currentMobile);
    }

    public function testTemplateWithoutTelephoneObject()
    {
        $user          = UserFactory::create();
        $event         = EventFactory::createEvent();
        $sheet         = SheetFactory::create($event);
        $participant   = ParticipantFactory::create($sheet, $user);
        $locale        = 'fr';

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $data         = ['69b3cde1' => []]; // no telephone object

        $block         = new Block('12', [], 'fr', 'fr');
        $editableText1 = new EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $block->addChild(1, '541f84d4', $editableText1);
        $templateData->addChild(1, '811f6edf', $block);

        // Mock
        $validateMobileProcessAccessChecker = $this->prophesize(ValidateMobileProcessAccessChecker::class);
        $participantInfoGuesser             = $this->prophesize(ParticipantInfoGuesser::class);

        $validateMobileProcessAccessChecker->allowToAccess($event, $user, $locale)
            ->shouldBeCalled()->willReturn(true);

        $participantInfoGuesser
            ->guessParticipantMobile($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('01000000000');

        $query = new PreUpdate($user, $participant, $event, $data, $templateData, $locale);

        $handler = new PreUpdateHandler(
            $validateMobileProcessAccessChecker->reveal(),
            $participantInfoGuesser->reveal()
        );

        $preUpdateView = $handler->handle($query);

        $this->assertEquals(PreUpdateHandler::MOBILE_VALIDATION_NOT_NEED, $preUpdateView->preUpdateState);
        $this->assertEquals(null, $preUpdateView->currentMobile);
    }
}
