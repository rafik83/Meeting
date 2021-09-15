<?php

namespace Proximum\Vimeet\Application\Command\Billing;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateInfoHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $dateTime    = new DateTime();
        $sheet       = new Sheet($event, $type, [], $user, $dateTime);
        $billingInfo = new BillingInfo($sheet);

        // Command
        $update = new UpdateInfo($billingInfo);
        $update->gender    = 'male';
        $update->lastname  = 'jean';
        $update->firstname = 'maurice';
        $update->function  = 'dj';
        $update->company   = 'bestsound';
        $update->phone     = '0197676545';
        $update->mobile    = '0697676545';
        $update->email     = 'jean.maurice@test.com';
        $update->street    = '25 rue de la tarte';
        $update->zipcode   = '75002';
        $update->city      = 'Paris';
        $update->country   = 'France';

        // Expected =
        $expectedBilling = new BillingInfo($sheet);
        $expectedBilling->prefill(
            'male',
            'maurice',
            'jean',
            'dj',
            'bestsound',
            '0197676545',
            '0697676545',
            'jean.maurice@test.com',
            new Address('25 rue de la tarte', '75002', 'Paris', 'France')
        );

        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $billingInfoRepository->add($expectedBilling)->shouldBeCalled();

        $handler = new UpdateInfoHandler($billingInfoRepository->reveal());
        $handler->handle($update);
    }
}
