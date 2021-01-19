<?php

namespace Proximum\Vimeet\Tests\Application\Query\User;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\Query\User\UserImpersonateViewQuery;
use Proximum\Vimeet\Application\Query\User\UserImpersonateViewQueryHandler;
use Proximum\Vimeet\Application\View\User\UserImpersonateView;
use Proximum\Vimeet\Application\View\User\UserView;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserImpersonateViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $fromUser = AdminFactory::create('admin@admin.com', 'John', 'McLane');
        $toUser   = UserFactory::create('user@vimeet.events');

        $account = new Account();
        $account->setFirstName('francois');
        $account->setLastName('bennet');
        $toUser->setAccount($account);

        $exitRouteName   = 'admin_sheet_details';
        $routeParameters = ['sheet' => 1, 'event' => 1];

        $impersonateUrlGenerator = $this->prophesize(ImpersonateUrlGeneratorInterface::class);

        $impersonateUrlGenerator->generateExit($exitRouteName, $routeParameters)
            ->shouldBeCalled()
            ->willReturn('_EXIT_URL_');

        $expectedUserImpersonateView = new UserImpersonateView(
            new UserView('John', 'McLane', 'admin@admin.com'),
            new UserView('Francois', 'BENNET', 'user@vimeet.events'),
            '_EXIT_URL_'
        );

        $query   = new UserImpersonateViewQuery($fromUser, $toUser, $exitRouteName, $routeParameters);
        $handler = new UserImpersonateViewQueryHandler($impersonateUrlGenerator->reveal());

        $userImpersonateView = $handler->handle($query);

        $this->assertEquals($expectedUserImpersonateView, $userImpersonateView);
    }
}
