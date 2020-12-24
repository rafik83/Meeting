<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Validator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Validator\CriteriaValidatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Validator\SheetAcceptedCriteriaValidator;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetAcceptedCriteriaValidatorTest extends TestCase
{
    public function testWithoutValidatorCriteria()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(false);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::ABSTAIN, $validator->isValid($sheet));
    }

    public function testWithValidatorCriteriaNo()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(true);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::NO, $validator->isValid($sheet));
    }

    public function testWithValidatorCriteriaYes()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(true);
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());
        $sheet->markAsAccepted();

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::YES, $validator->isValid($sheet));
    }
}
