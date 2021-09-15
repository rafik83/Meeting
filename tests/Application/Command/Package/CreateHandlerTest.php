<?php

namespace Proximum\Vimeet\Tests\Application\Command\Package;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Application\Command\Package\CreateHandler;
use Proximum\Vimeet\Application\Command\Package\CreateResult;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $defaultLabels = [
            'plans' => [
                'fr' => 'Forfait',
                'en' => 'Plans',
            ],
            'participant_and_planning' => [
                'fr' => 'Participant et planning',
                'en' => 'Participant and Planning',
            ],
            'options' => [
                'fr' => 'Options',
                'en' => 'Options',
            ],
        ];

        $dateTime = new \DateTimeImmutable();

        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en']);

        $expected = new Package($event, 'Lorem ipsum', $dateTime);
        $expected->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $expected->translate('en', 'Plans', 'Participant and Planning', 'Options');

        $command        = new Create();
        $command->event = $event;
        $command->title = 'Lorem ipsum';

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);

        $packageRepository->add(Argument::that(function (Package $package) use ($expected) {
            $this->assertEquals($expected, $package);

            return true;
        }))->shouldBeCalled();

        $handler = new CreateHandler($packageRepository->reveal(), $dateTime, $defaultLabels);

        $this->assertEquals(new CreateResult($expected), $handler->handle($command));
    }
}
