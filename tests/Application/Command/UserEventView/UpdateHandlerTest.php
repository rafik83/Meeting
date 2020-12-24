<?php

namespace Proximum\Vimeet\Tests\Application\Command\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\ElasticSearchPersisterInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Command\UserEventView\UpdateHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;

class UpdateHandlerTest extends TestCase
{
    private $event, $user, $elasticSearchPersister, $userEventViewsFactory, $updateHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->getId()->willReturn(42);

        $this->user = $this->prophesize(User::class);
        $this->user->getId()->willReturn(1337);

        $this->elasticSearchPersister = $this->prophesize(ElasticSearchPersisterInterface::class);
        $this->userEventViewsFactory = $this->prophesize(UserEventViewsFactory::class);

        $this->updateHandler = new UpdateHandler(
            $this->elasticSearchPersister->reveal(),
            $this->userEventViewsFactory->reveal()
        );
    }

    public function testDelete()
    {
        $this
            ->userEventViewsFactory
            ->getByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->elasticSearchPersister
            ->deleteIds('user_event', ['42_1337'])
            ->shouldBeCalled()
        ;

        $this->updateHandler->handle(new Update($this->user->reveal(), $this->event->reveal()));
    }

    public function testPersist()
    {
        $userEventViews = [
            new UserEventView(
                42,
                1337,
                'Korben',
                'Dallas',
                'Korben.dallas@fifth.element',
                'fr',
                false,
                false,
                [['id' => 999]],
                []
            ),
        ];

        $this
            ->userEventViewsFactory
            ->getByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventViews)
        ;

        $this
            ->elasticSearchPersister
            ->deleteIds('user_event', ['42_1337'])
            ->shouldNotBeCalled()
        ;

        $this
            ->elasticSearchPersister
            ->persist('id', $userEventViews)
            ->shouldBeCalled()
        ;

        $this->updateHandler->handle(new Update($this->user->reveal(), $this->event->reveal()));
    }
}
