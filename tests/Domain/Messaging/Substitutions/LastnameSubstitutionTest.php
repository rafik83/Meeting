<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Messaging\Substitutions\OwnerLastnameSubstitution;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class LastnameSubstitutionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubstitute()
    {
        $user    = UserFactory::create('vimeet@proximum.com');
        $account = new Account();
        $account->setLastName('larose');
        $user->setAccount($account);
        $event  = EventFactory::createEvent('Proximum');
        $sheet  = SheetFactory::create($event, $user);
        $locale = 'fr';

        $substitution = new OwnerLastnameSubstitution();
        $firstname    = $substitution->getValue($sheet, $locale);

        $this->assertEquals('larose', $firstname);
    }
}
