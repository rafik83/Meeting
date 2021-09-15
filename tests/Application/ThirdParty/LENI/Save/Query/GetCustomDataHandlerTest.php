<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestDataHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomDataHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetCustomDataHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sendingRequestDataHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $hasUserSheetStateChangedQueryHandler;

    public function setUp()
    {
        $this->sendingRequestDataHandler = $this->prophesize(SendingRequestDataHandler::class);
        $this->hasUserSheetStateChangedQueryHandler = $this->prophesize(HasUserSheetStateChangedQueryHandler::class);
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    public function testHandleWithNoLeniUserId()
    {
        $this
            ->hasUserSheetStateChangedQueryHandler
            ->handle(
                new HasUserSheetStateChangedQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API', 'Inscrit' => 'Inscrit']
                )
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API', 'Inscrit' => 'Inscrit'],
                    false
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $getCustomDataHandler = new GetCustomDataHandler(
            $this->sendingRequestDataHandler->reveal(),
            $this->hasUserSheetStateChangedQueryHandler->reveal()
        );

        $this->assertEquals(
            ['whatever' => 'value', 'EvenementOrigine' => 'API', 'Inscrit' => 'Inscrit'],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value']
                )
            )
        );
    }

    public function testHandleWithNoLeniUserIdWithSendingRequestData()
    {
        $this
            ->hasUserSheetStateChangedQueryHandler
            ->handle(
                new HasUserSheetStateChangedQuery(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API', 'Inscrit' => 'Inscrit']
                )
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value', 'EvenementOrigine' => 'API', 'Inscrit' => 'Inscrit'],
                    false
                )
            )
            ->shouldBeCalled()
            ->willReturn(['codeCommunication' => '5W3ORMI3', 'id' => '9A74DF80-1B13-9B68-74A8-1D956F54FECB'])
        ;

        $getCustomDataHandler = new GetCustomDataHandler(
            $this->sendingRequestDataHandler->reveal(),
            $this->hasUserSheetStateChangedQueryHandler->reveal()
        );

        $this->assertEquals(
            [
                'whatever' => 'value',
                'EvenementOrigine' => 'API',
                'Inscrit' => 'Inscrit',
                'SendingRequests' => [
                    'codeCommunication' => '5W3ORMI3',
                    'id' => '9A74DF80-1B13-9B68-74A8-1D956F54FECB',
                ]
            ],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['whatever' => 'value']
                )
            )
        );
    }

    public function testHandleWithLeniUserId()
    {
        $this
            ->hasUserSheetStateChangedQueryHandler
            ->handle(
                new HasUserSheetStateChangedQuery(
                    $this->event->reveal(), $this->user->reveal(), ['Id' => 'GLP971', 'whatever' => 'value']
                )
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['Id' => 'GLP971', 'whatever' => 'value'],
                    false
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $getCustomDataHandler = new GetCustomDataHandler(
            $this->sendingRequestDataHandler->reveal(),
            $this->hasUserSheetStateChangedQueryHandler->reveal()
        );

        $this->assertEquals(
            ['Id' => 'GLP971', 'whatever' => 'value'],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['Id' => 'GLP971', 'whatever' => 'value']
                )
            )
        );
    }

    public function testHandleWithLeniUserIdAndUserSheetStateChanged()
    {
        $this
            ->hasUserSheetStateChangedQueryHandler
            ->handle(
                new HasUserSheetStateChangedQuery(
                    $this->event->reveal(), $this->user->reveal(), ['Id' => 'GLP971', 'whatever' => 'value']
                )
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->sendingRequestDataHandler
            ->handle(
                new SendingRequestData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['Id' => 'GLP971', 'whatever' => 'value', 'Inscrit' => 'Inscrit'],
                    true
                )
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $getCustomDataHandler = new GetCustomDataHandler(
            $this->sendingRequestDataHandler->reveal(),
            $this->hasUserSheetStateChangedQueryHandler->reveal()
        );

        $this->assertEquals(
            ['Id' => 'GLP971', 'whatever' => 'value', 'Inscrit' => 'Inscrit'],
            $getCustomDataHandler->handle(
                new GetCustomData(
                    $this->event->reveal(),
                    $this->user->reveal(),
                    ['Id' => 'GLP971', 'whatever' => 'value']
                )
            )
        );
    }
}
