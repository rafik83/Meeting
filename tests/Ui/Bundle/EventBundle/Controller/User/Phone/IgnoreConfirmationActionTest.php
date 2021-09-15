<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmation;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmationHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone\IgnoreConfirmationAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IgnoreConfirmationActionTest extends TestCase
{
    /** @var IgnoreConfirmationHandler */
    private $ignoreConfirmationHandler;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Event */
    private $event;

    /** @var Participant */
    private $participant;

    /** @var IgnoreConfirmation */
    private $ignoreConfirmation;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->participant = ParticipantFactory::create(SheetFactory::create($this->event));

        $this->ignoreConfirmation = new IgnoreConfirmation($this->event, $this->participant);
        $this->ignoreConfirmationHandler = $this->prophesize(IgnoreConfirmationHandler::class);

        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testAccessDeniedException()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(false);

        $action = new IgnoreConfirmationAction(
            $this->ignoreConfirmationHandler->reveal(),
            $this->authorizationChecker->reveal()
        );

        $action(SheetFactory::create($this->event, $this->participant->getUser()), $this->participant);
    }

    public function testAction()
    {
        $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')->shouldBeCalled()->willReturn(true);

        $this->ignoreConfirmationHandler->handle($this->ignoreConfirmation)->shouldBeCalled();

        $expectedResponse = new JsonResponse([]);

        $action = new IgnoreConfirmationAction(
            $this->ignoreConfirmationHandler->reveal(),
            $this->authorizationChecker->reveal()
        );

        $response = $action(SheetFactory::create($this->event, $this->participant->getUser()), $this->participant);

        $this->assertEquals($expectedResponse, $response);
    }
}
