<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\RemoveAlreadyImportedReferences;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class RemoveAlreadyImportedReferencesTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $sheetAlreadyImportedExtraData = $this->prophesize(ExtraData::class);
        $sheetAlreadyImportedExtraData->getValue()->shouldBeCalled()->willReturn('3334444');

        $extraDataRepositoryInterface = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepositoryInterface
            ->getExtraDataValuesForEvent(
                $event,
                'comexposium_registration_reference',
                ['111222333', '3334444']
            )
            ->shouldBeCalled()
            ->willReturn([$sheetAlreadyImportedExtraData->reveal()])
        ;

        $removeAlreadyImportedReferences = new RemoveAlreadyImportedReferences($extraDataRepositoryInterface->reveal());
        $result = $removeAlreadyImportedReferences->handle($event->reveal(), ['111222333', '3334444']);

        $this->assertEquals(['111222333'], $result);
    }
}
