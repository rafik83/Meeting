<?php

namespace Proximum\Vimeet\Domain\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class RequestTransformableTest extends TestCase
{
    public function testOneToOneWithNoPreferenceNoForcing()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);

        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithNoPreferenceNoForcing()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));
        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithPreferenceNoForcing()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $participantOne = new Participant($toSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($toSheet, $user, [], true, $datetime);

        $toSheet->addParticipant($participantOne);
        $toSheet->addParticipant($participantTwo);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo, $participantOne], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testManyToManyWithNoPreferenceNoForcing()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);

        $participantOne = new Participant($fromSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($fromSheet, $user, [], true, $datetime);
        $participantThree = new Participant($toSheet, $user, [], true, $datetime);
        $participantFour = new Participant($toSheet, $user, [], true, $datetime);

        $fromSheet->addParticipant($participantOne);
        $fromSheet->addParticipant($participantTwo);

        $toSheet->addParticipant($participantThree);
        $toSheet->addParticipant($participantFour);

        $request = new Meeting\Request($fromSheet, [$participantOne, $participantTwo], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToOneWithNoPreferenceForcingToSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);

        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithNoPreferenceForcingToSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);

        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));
        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithPreferenceForcingToSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);

        $participantOne = new Participant($toSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($toSheet, $user, [], true, $datetime);

        $toSheet->addParticipant($participantOne);
        $toSheet->addParticipant($participantTwo);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo, $participantOne], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testManyToManyWithNoPreferenceForcingToSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);

        $participantOne = new Participant($fromSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($fromSheet, $user, [], true, $datetime);
        $participantThree = new Participant($toSheet, $user, [], true, $datetime);
        $participantFour = new Participant($toSheet, $user, [], true, $datetime);

        $fromSheet->addParticipant($participantOne);
        $fromSheet->addParticipant($participantTwo);

        $toSheet->addParticipant($participantThree);
        $toSheet->addParticipant($participantFour);

        $request = new Meeting\Request($fromSheet, [$participantOne, $participantTwo], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToOneWithNoPreferenceForcingFromSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);

        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithNoPreferenceForcingFromSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));
        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithPreferenceForcingFromSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $participantOne = new Participant($toSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($toSheet, $user, [], true, $datetime);

        $toSheet->addParticipant($participantOne);
        $toSheet->addParticipant($participantTwo);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo, $participantOne], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testManyToManyWithNoPreferenceForcingFromSheet(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $participantOne = new Participant($fromSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($fromSheet, $user, [], true, $datetime);
        $participantThree = new Participant($toSheet, $user, [], true, $datetime);
        $participantFour = new Participant($toSheet, $user, [], true, $datetime);

        $fromSheet->addParticipant($participantOne);
        $fromSheet->addParticipant($participantTwo);

        $toSheet->addParticipant($participantThree);
        $toSheet->addParticipant($participantFour);

        $request = new Meeting\Request($fromSheet, [$participantOne, $participantTwo], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(false, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToOneWithNoPreferenceForcingBoth(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);

        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithNoPreferenceForcingBoth(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));
        $toSheet->addParticipant(new Participant($toSheet, $user, [], true, $datetime));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testOneToManyWithPreferenceForcingBoth(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $participantOne = new Participant($toSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($toSheet, $user, [], true, $datetime);

        $toSheet->addParticipant($participantOne);
        $toSheet->addParticipant($participantTwo);

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));

        $request = new Meeting\Request($fromSheet, [], $toSheet, [$participantTwo, $participantOne], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    public function testManyToManyWithNoPreferenceForcingBoth(): void
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $user      = new User('user@gmail.com', '', '', $locale);
        $datetime  = new \DateTime();
        $fromSheet = SheetFactory::create($event);
        $toSheet   = SheetFactory::create($event);
        $this->allSheetParticipantsAreAssignedToMeeting($toSheet);
        $this->allSheetParticipantsAreAssignedToMeeting($fromSheet);

        $participantOne = new Participant($fromSheet, $user, [], true, $datetime);
        $participantTwo = new Participant($fromSheet, $user, [], true, $datetime);
        $participantThree = new Participant($toSheet, $user, [], true, $datetime);
        $participantFour = new Participant($toSheet, $user, [], true, $datetime);

        $fromSheet->addParticipant($participantOne);
        $fromSheet->addParticipant($participantTwo);

        $toSheet->addParticipant($participantThree);
        $toSheet->addParticipant($participantFour);

        $request = new Meeting\Request($fromSheet, [$participantOne, $participantTwo], $toSheet, [], $datetime, $user, $event);
        $this->assertEquals(true, Meeting\TransformableRequest::isTransformable($request));
    }

    protected function allSheetParticipantsAreAssignedToMeeting(Sheet $toSheet): void
    {
        $type = $toSheet->getType();
        $type->update(
            $type->getPosition(),
            $type->isHidden(),
            $type->getAvailabilityType(),
            $type->getNumberOfMeetingsPerPlanning(),
            $type->canMoveMeeting(),
            $type->canRemoveMeeting(),
            true
        );
    }
}
