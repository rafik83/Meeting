<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Encryption\UserEventProtectedKeyPasswordGetterAdapter;

class UserEventProtectedKeyPasswordGetterAdapterTest extends TestCase
{
    public function testGetProtectedKeyPasswordByEventAndUser()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(13317);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(963);

        $userEventProtectedKeyPasswordGetter = new UserEventProtectedKeyPasswordGetterAdapter('_my-very_secret_KEY');

        $result = $userEventProtectedKeyPasswordGetter->getProtectedKeyPasswordByEventAndUser(
            $event->reveal(),
            $user->reveal()
        );

        $this->assertEquals('9c48920141f130cacdac4250582e9c2ef56fc515518c1783af776f43806d3da4', $result);
    }
}
