<?php

namespace Application\Command\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Tip\Update;
use Proximum\Vimeet\Application\Command\Tip\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip = new Tip('tipTitle', null, true, false, true, true, false, false, false, true, false, false, $dateTime);
        $tipTranslation = new TipTranslation($tip, $dateTime, 'title_en', 'en', 'content_en');
        $tip->setTranslation('en', 'title_en', 'content_en', $dateTime);

        $command = new Update($tip);
        $command->translations = [
            'en' => [
                'title'   => 'title_fr',
                'content' => 'content_fr',
                'locale'  => 'fr',
            ],
        ];

        $tipRepository->removeTranslation($tipTranslation)->shouldBeCalled();

        $tipRepository->set($tip)->shouldBeCalled();

        $handler = new UpdateHandler($tipRepository->reveal(), $dateTime);
        $handler->handle($command);
    }

    public function testHandleUpdate()
    {
        $dateTime = new \DateTime();
        $tip = new Tip('tipTitle', null, true, true, true, true, true, true, true, true, true, false, $dateTime);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $expectedTip = new Tip('newTipTitle', null, false, true, false, true, false, false, false, true, true, false, $dateTime);
        $tipRepository->set($expectedTip)->shouldBeCalled();

        $command = new Update($tip);
        $command->title = 'newTipTitle';
        $command->onMeetingManagement = false;
        $command->onPrintPlanning = false;
        $command->onAgenda = false;
        $command->onPackage = false;
        $command->onContacts = false;
        $command->translations = [];

        $handler = new UpdateHandler($tipRepository->reveal(), $dateTime);
        $handler->handle($command);
    }
}
