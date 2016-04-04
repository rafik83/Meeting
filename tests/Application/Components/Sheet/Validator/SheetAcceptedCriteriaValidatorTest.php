<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Sheet\Validator;

use Proximum\Vimeet\Application\Components\Sheet\Validator\CriteriaValidatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Validator\SheetAcceptedCriteriaValidator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetAcceptedCriteriaValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testWithoutValidatorCriteria()
    {
        $event = new Event();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(false);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::ABSTAIN, $validator->isValid($sheet));
    }

    public function testWithValidatorCriteriaNo()
    {
        $event = new Event();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(true);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::NO, $validator->isValid($sheet));
    }

    public function testWithValidatorCriteriaYes()
    {
        $event = new Event();
        $type  = new Type($event);
        $type->getValidationCriteria()->setSheetAccepted(true);

        $sheet = new Sheet($event, $type, [], [], new \DateTime());
        $sheet->markAsAccepted();

        $validator = new SheetAcceptedCriteriaValidator();

        $this->assertEquals(CriteriaValidatorInterface::YES, $validator->isValid($sheet));
    }
}
