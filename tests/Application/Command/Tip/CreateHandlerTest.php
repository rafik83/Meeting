<?php

namespace Application\Command\Tip;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Tip\Create;
use Proximum\Vimeet\Application\Command\Tip\CreateHandler;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $command = new Create(['fr', 'en']);
        $command->onCatalog = true;
        $command->onMeetingManagement = true;
        $command->onPrintPlanning = true;
        $command->title = 'tipTitle';
        $tip = new Tip('tipTitle', null, true, true, true, false, false, false, false, false, false, false, $dateTime);
        $command->translations = [
            'locale_1' => [
                'locale' => 'locale_1',
                'content' => 'content_1',
                'title' => 'title_1',
            ],
        ];

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip->setTranslation('locale_1', 'title_1', 'content_1', $dateTime);

        $tipRepository->add(Argument::that(function (Tip $tip) {
            return
                'title_1' === $tip->getTranslationTitle('locale_1')
                &&
                'content_1' === $tip->getTranslationContent('locale_1');
        }))
        ->shouldBeCalled();

        $handler = new CreateHandler($tipRepository->reveal(), $dateTime);

        $handler->handle($command);
    }
}
