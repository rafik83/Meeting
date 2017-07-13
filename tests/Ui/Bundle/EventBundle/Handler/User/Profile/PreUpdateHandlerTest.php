<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\User\Profile;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Session\UpdateParticipantSessionManager;
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
        $updateParticipantSessionManager    = $this->prophesize(UpdateParticipantSessionManager::class);

        $validateMobileProcessAccessChecker->allowToAccess($event, $user, $locale)
            ->shouldBeCalled()->willReturn(true);

        $participantInfoGuesser
            ->guessParticipantMobile($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('01000000000');

        $updateParticipantSessionManager->set(
            $sheet,
            $participant,
            $currentMobile
        )->shouldBeCalled();

        $query = new PreUpdate($user, $participant, $event, $data, $templateData, $locale);

        $handler = new PreUpdateHandler(
            $validateMobileProcessAccessChecker->reveal(),
            $participantInfoGuesser->reveal(),
            $updateParticipantSessionManager->reveal()
        );

        $preUpdateState = $handler->handle($query);

        $this->assertEquals(PreUpdateHandler::MOBILE_VALIDATION_NEEDED, $preUpdateState);
    }
}
