<?php


namespace Proximum\Vimeet\Tests\Domain\Unavailability\SystemGenerator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\GenerateParticipantProductSystemUnavailabilities;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\Generator;

class GenerateParticipantProductSystemUnavailabilitiesTest extends TestCase
{
    public function test()
    {
        $event = $this->prophesize(Event::class);
        $product = $this->prophesize(Product::class);
        $product->getEvent()->willReturn($event);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByParticipantProduct($product->reveal())->shouldBeCalled()
            ->willReturn([$user1, $user2]);

        $generator = $this->prophesize(Generator::class);
        $generator->generateSystemUnavailability($event->reveal(), $user1->reveal())->shouldBeCalled();
        $generator->generateSystemUnavailability($event->reveal(), $user2->reveal())->shouldBeCalled();

        $generateParticipantProductSystemUnavailabilities = new GenerateParticipantProductSystemUnavailabilities($userRepository->reveal(), $generator->reveal());

        $generateParticipantProductSystemUnavailabilities($product->reveal());
    }
}
