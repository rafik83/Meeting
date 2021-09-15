<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Infrastructure\Adapter\ImpersonateUrlGeneratorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImpersonateUrlGeneratorAdapterTest extends TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $admin = new Admin('admin@proximum.com', 'salt', 'password', 'fr', 'Jean', 'Dupond', 'ROLE_ADMIN', $now);
        $user  = UserFactory::create();
        $event = EventFactory::createEvent();

        $impersonate       = $this->prophesize(Impersonate::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $urlGenerator      = $this->prophesize(UrlGeneratorInterface::class);

        $impersonate->getEncodedToken($admin, $user)->shouldBeCalled()->willReturn('_TOKEN_');

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event,
            'event_sheet',
            array_merge(['_switchto' => '_TOKEN_'], ['sheet' => 1])
        )->shouldBeCalled()->willReturn('_IMPERSONATE_LINK_');

        $impersonateUrlGenerator = new ImpersonateUrlGeneratorAdapter(
            $impersonate->reveal(),
            $eventUrlGenerator->reveal(),
            $urlGenerator->reveal()
        );

        $impersonateUrl = $impersonateUrlGenerator->generate($admin, $user, $event, 'event_sheet', ['sheet' => 1]);

        $this->assertEquals('_IMPERSONATE_LINK_', $impersonateUrl);
    }
}
