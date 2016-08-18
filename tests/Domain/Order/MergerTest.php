<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\BillingInfo;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class MergerTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $orderBillingInfo = new Order\BillingInfo(
            'lastname',
            'firstname',
            'function',
            'phone',
            'mobile',
            'company',
            'email@email.com',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber',
            'gender'
        );

        $plan        = Product::createPlan($event, 'plan', '', 200, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1250, 20);
        $option      = Product::createOption($event, 'option', '', 99, 50, 10, 20, true);

        // Setup
        $orderOne = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-5 day'));
        $orderOne->addRow(new Order\Row($orderOne, 1, $plan));
        $orderOne->addRow(new Order\Row($orderOne, 2, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, $option));
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, true, $orderBillingInfo, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, $participant));
        $orderTwo->addRow(new Order\Row($orderTwo, 3, $option));
        $sheet->addOrder($orderTwo);

        $orderMerger = new Merger();
        $order       = $orderMerger->merge([$orderOne, $orderTwo]);

        $this->assertEquals(1, $order->getRowForProduct($plan)->getQuantity());
        $this->assertEquals(1, $order->getRowForProduct($participant)->getQuantity());
        $this->assertEquals(4, $order->getRowForProduct($option)->getQuantity());
    }
}
