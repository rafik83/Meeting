<?php

namespace Proximum\Vimeet\Tests\Application\Command\Partner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Partner\Update;
use Proximum\Vimeet\Application\Command\Partner\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $dateTime = new \DateTime();
        $event = EventFactory::createEvent();
        $oldType = new Type($event);
        $type = new Type($event);

        $partner = new Admin('partner@vimeet.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_PARTNER, $dateTime);
        $partner->addEvent($event);
        $partner->addType($oldType);

        $command = new Update($partner);
        $command->types = [$oldType, $type];

        $expectedPartner = new Admin('partner@vimeet.com', '__salt__', 'encoded_password', 'fr', 'toto', 'tata', Admin::ROLE_PARTNER, $dateTime);

        $expectedPartner->addEvent($event);
        $expectedPartner->addType($type);
        $expectedPartner->addType($oldType);

        // Mock
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);

        $adminRepository->emailExists($command->email)->shouldNotBeCalled();
        $adminRepository->set($expectedPartner)->shouldBeCalled();

        // Command
        $handler = new UpdateHandler($adminRepository->reveal());

        $handler->handle($command);
    }
}
