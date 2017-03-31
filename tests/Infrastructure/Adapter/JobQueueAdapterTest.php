<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\JobQueueAdapter;

class JobQueueAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testExportOrdersForEvent()
    {
        $event  = $this->prophesize(Event::class);
        $event->getId()->willReturn(1234);
        $admin  = $this->prophesize(Admin::class);
        $admin->getEmail()->willReturn('email@admin.fr');
        $locale = 'fr';

        $entityManager = $this->prophesize(EntityManager::class);
        $expectedJob   = new Job('vimeet:order:export', [1234, 'email@admin.fr', 'fr']);
        $entityManager->persist($expectedJob)->shouldBeCalled();
        $entityManager->flush($expectedJob)->shouldBeCalled();

        $jobQueueAdapter = new JobQueueAdapter($entityManager->reveal());
        $jobQueueAdapter->exportOrdersForEvent($event->reveal(), $admin->reveal(), $locale);
    }
}
