<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Messaging\Substitutions\OwnerLastnameSubstitution;
use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class LastnameSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $user    = UserFactory::create('vimeet@proximum.com');
        $account = new Account();
        $account->setLastName('Martin');
        $user->setAccount($account);
        $event  = EventFactory::createEvent('Proximum');
        $sheet  = SheetFactory::create($event, $user);
        $locale = 'fr';

        $substitution = new OwnerLastnameSubstitution();
        $firstname    = $substitution->getValue($sheet, $locale);

        $this->assertEquals('MARTIN', $firstname);
    }
}
