<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rule;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Rule\SeeWhat;
use Proximum\Vimeet\Application\Command\Rule\SeeWhatHandler;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SeeWhatHandlerTest extends TestCase
{
    /**
     * Change the seeWhat of the rule
     */
    public function testHandle()
    {
        // Init
        $event   = EventFactory::createEvent();
        $seer    = new Type($event);
        $seeable = new Type($event);
        $rule    = new Rule($event, $seer, $seeable, ['participant_position'], 2, false);
        $seeWhat = new SeeWhat($rule);
        $seeWhat->priority = 2;
        $seeWhat->seeWhat  = ['participant_firstname', 'participant_lastname'];
        $seeWhat->requestAutomaticallyTransformedIntoMeeting = true;
        $seeWhat->phoneAccessMinEvaluation = 1;
        $seeWhat->emailAccessMinEvaluation = 2;
        $seeWhat->sendEmailMinEvaluation = 3;

        // Mock
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);

        // Handler
        $handler = new SeeWhatHandler($ruleRepository->reveal());
        $handler->handle($seeWhat);

        $ruleRepository->update(Argument::that(function (Rule $rule) use ($event, $seer, $seeable) {
            return
                $rule->getEvent() === $event
                && $rule->getSeer() === $seer
                && $rule->getSeeable() === $seeable
                && $rule->getWhat() === ['participant_firstname', 'participant_lastname']
                && $rule->getPriority() === 2
                && $rule->getRequestAutomaticallyTransformedIntoMeeting() === true
                && $rule->getPhoneAccessMinEvaluation() === 1
                && $rule->getEmailAccessMinEvaluation() === 2
                && $rule->getSendEmailMinEvaluation() === 3;
        }))->shouldHaveBeenCalled();
    }

    /**
     * Change the SeeWhat and the priority of the rule
     */
    public function testHandleWithNewPriority()
    {
        // Init
        $event   = EventFactory::createEvent();
        $seer    = new Type($event);
        $seable  = new Type($event);
        $rule    = new Rule($event, $seer, $seable, ['participant_position'], 2, false);
        $seeWhat = new SeeWhat($rule);
        $seeWhat->priority = 4;
        $seeWhat->seeWhat  = ['participant_firstname', 'participant_lastname'];
        $seeWhat->requestAutomaticallyTransformedIntoMeeting = true;

        $expectedRule = new Rule($event, $seer, $seable, ['participant_firstname', 'participant_lastname'], 4, true);

        // Mock
        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->update($expectedRule)->shouldBeCalled();

        // Handler
        $handler = new SeeWhatHandler($ruleRepository->reveal());
        $handler->handle($seeWhat);
    }
}
