<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\OwnerFirstnameSubstitution;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class FirstnameSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $user    = UserFactory::create('vimeet@proximum.com');
        $account = new Account();
        $account->setFirstName('vincent');
        $user->setAccount($account);
        $event  = EventFactory::createEvent('Proximum');
        $sheet  = SheetFactory::create($event, $user);
        $locale = 'fr';

        $substitution = new OwnerFirstnameSubstitution();
        $firstname    = $substitution->getValue($sheet, $locale);

        $this->assertEquals('Vincent', $firstname);
    }
}
