<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingRequest\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\Import;
use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\ImportHandler;
use Proximum\Vimeet\Application\Exception\Import\InvalidEmailException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\MultipleParticipantsFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\UserNotFoundException;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\MeetingRequest\Import\MeetingRequestRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ImportHandlerTest extends TestCase
{
    private ObjectProphecy $fileStorage;
    private ObjectProphecy $serializer;
    private ObjectProphecy $validator;
    private ObjectProphecy $userRepository;
    private ObjectProphecy $participantRepository;
    private ObjectProphecy $meetingRequestRepository;
    private ObjectProphecy $event;

    public function setUp()
    {
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->serializer = $this->prophesize(SerializerAdapterInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $this->meetingRequestImportHandler = new ImportHandler(
            $this->fileStorage->reveal(),
            $this->serializer->reveal(),
            $this->validator->reveal(),
            $this->userRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->meetingRequestRepository->reveal(),
            new \DateTime()
        );

        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleImport()
    {
        // Given I have a file suitable for meeting requests import
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'csv'])
            ->getMock();
        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('a;b;c');

        // And file parsing produces 2 rows
        $this->serializer
            ->deserialize(
                'a;b;c',
                Argument::type('string'),
                Argument::type('string'),
                Argument::withEntry('event', $this->event->reveal())
            )
            ->shouldBeCalled()
            ->willReturn([
                $this->makeRow('user1@example.net', 'user2@example.net'),
                $this->makeRow('user1@example.net', 'user3@example.net'),
            ]);
        // And emails are valid
        $this->validator
            ->validate(Argument::containingString('@example.net'), ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        // And there is a user for each email
        $user1 = $this->mockUser('user1@example.net');
        $user2 = $this->mockUser('user2@example.net');
        $user3 = $this->mockUser('user3@example.net');

        // And there is a participant for each user
        $participant1 = $this->mockParticipant($user1->reveal(), 1);
        $participant2 = $this->mockParticipant($user2->reveal(), 2);
        $participant3 = $this->mockParticipant($user3->reveal(), 3);

        // And there is no existing meeting request
        $this->meetingRequestRepository
            ->getRequestBetweenSheets(Argument::type(Sheet::class), Argument::type(Sheet::class))
            ->willReturn(null);

        // And I have a command with this file and a charset in a specific event
        $command = new Import($this->event->reveal());
        $command->file = $file;
        $command->charset = Charset::UTF_8;

        // When I call import handler
        $this->meetingRequestImportHandler->handle($command);

        // Then a meeting request should be added
        $this->meetingRequestRepository
            ->add(Argument::that($this->checkMeetingRequestBetween($participant1->reveal()->getSheet(), $participant2->reveal()->getSheet())))
            ->shouldHaveBeenCalled();
        $this->meetingRequestRepository
            ->add(Argument::that($this->checkMeetingRequestBetween($participant1->reveal()->getSheet(), $participant3->reveal()->getSheet())))
            ->shouldHaveBeenCalled();
    }

    public function testThrowExceptionIfEmailIsInvalid()
    {
        // Given I have a file suitable for meeting requests import
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'csv'])
            ->getMock();
        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('a;b;c');

        // And file parsing produces 1 row
        $this->serializer
            ->deserialize(
                'a;b;c',
                Argument::type('string'),
                Argument::type('string'),
                Argument::withEntry('event', $this->event->reveal())
            )
            ->shouldBeCalled()
            ->willReturn([
                $this->makeRow('user1@example.net', 'user2@example.net'),
            ]);
        // And emails are invalid
        $this->validator
            ->validate(Argument::containingString('@example.net'), ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([$this->prophesize(ConstraintViolationInterface::class)->reveal()]));

        // And I have a command with this file and a charset in a specific event
        $command = new Import($this->event->reveal());
        $command->file = $file;
        $command->charset = Charset::UTF_8;

        // A InvalidEmailException should be thrown
        $this->expectException(InvalidEmailException::class);

        // When I call import handler
        $this->meetingRequestImportHandler->handle($command);
    }

    public function testThrowExceptionIfUserNotFound()
    {
        // Given I have a file suitable for meeting requests import
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'csv'])
            ->getMock();
        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('a;b;c');

        // And file parsing produces 1 row
        $this->serializer
            ->deserialize(
                'a;b;c',
                Argument::type('string'),
                Argument::type('string'),
                Argument::withEntry('event', $this->event->reveal())
            )
            ->shouldBeCalled()
            ->willReturn([
                $this->makeRow('user1@example.net', 'user2@example.net'),
            ]);
        // And emails are valid
        $this->validator
            ->validate(Argument::containingString('@example.net'), ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        // And there is no user with email user1@example.net
        $this->userRepository->findByEmail('user1@example.net')->willReturn(null);

        // And I have a command with this file and a charset in a specific event
        $command = new Import($this->event->reveal());
        $command->file = $file;
        $command->charset = Charset::UTF_8;

        // A UserNotFoundException should be thrown
        $this->expectException(UserNotFoundException::class);

        // When I call import handler
        $this->meetingRequestImportHandler->handle($command);
    }

    public function testThrowExceptionIfMultipleParticipantsFound()
    {
        // Given I have a file suitable for meeting requests import
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'csv'])
            ->getMock();
        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('a;b;c');

        // And file parsing produces 1 row
        $this->serializer
            ->deserialize(
                'a;b;c',
                Argument::type('string'),
                Argument::type('string'),
                Argument::withEntry('event', $this->event->reveal())
            )
            ->shouldBeCalled()
            ->willReturn([
                $this->makeRow('user1@example.net', 'user2@example.net'),
            ]);
        // And emails are valid
        $this->validator
            ->validate(Argument::containingString('@example.net'), ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        // And there is a user for each email
        $user1 = $this->mockUser('user1@example.net');
        $user2 = $this->mockUser('user2@example.net');

        // And $user1 has 2 participants
        $this->participantRepository
            ->getParticipantsByUserForEvent($user1->reveal(), $this->event->reveal())
            ->willReturn([$this->prophesize(Participant::class)->reveal(), $this->prophesize(Participant::class)->reveal()]);

        // And I have a command with this file and a charset in a specific event
        $command = new Import($this->event->reveal());
        $command->file = $file;
        $command->charset = Charset::UTF_8;

        // A MultipleParticipantsFoundException should be thrown
        $this->expectException(MultipleParticipantsFoundException::class);

        // When I call import handler
        $this->meetingRequestImportHandler->handle($command);
    }

    public function testThrowExceptionIfParticipantNotFound()
    {
        // Given I have a file suitable for meeting requests import
        $file = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'csv'])
            ->getMock();
        $this->fileStorage->getContents($file)->shouldBeCalled()->willReturn('a;b;c');

        // And file parsing produces 1 row
        $this->serializer
            ->deserialize(
                'a;b;c',
                Argument::type('string'),
                Argument::type('string'),
                Argument::withEntry('event', $this->event->reveal())
            )
            ->shouldBeCalled()
            ->willReturn([
                $this->makeRow('user1@example.net', 'user2@example.net'),
            ]);
        // And emails are valid
        $this->validator
            ->validate(Argument::containingString('@example.net'), ValidatorInterface::VALIDATOR_EMAIL_TYPE)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        // And there is a user for each email
        $user1 = $this->mockUser('user1@example.net');
        $user2 = $this->mockUser('user2@example.net');

        // And $user1 has no participant
        $this->participantRepository
            ->getParticipantsByUserForEvent($user1->reveal(), $this->event->reveal())
            ->willReturn([]);

        // And I have a command with this file and a charset in a specific event
        $command = new Import($this->event->reveal());
        $command->file = $file;
        $command->charset = Charset::UTF_8;

        // A UserNotFoundException should be thrown
        $this->expectException(ParticipantNotFoundException::class);

        // When I call import handler
        $this->meetingRequestImportHandler->handle($command);
    }

    private function makeRow(string $emailFrom, string $emailTo): MeetingRequestRow
    {
        $row = new MeetingRequestRow();
        $row->emailFrom = $emailFrom;
        $row->emailTo = $emailTo;

        return $row;
    }

    private function mockUser(string $email): ObjectProphecy
    {
        $user = $this->prophesize(User::class);
        $this->userRepository->findByEmail($email)->willReturn($user->reveal());

        return $user;
    }

    private function mockParticipant(User $user, int $sheetId): ObjectProphecy
    {
        $participant = $this->prophesize(Participant::class);
        $this->participantRepository
            ->getParticipantsByUserForEvent($user, $this->event->reveal())
            ->willReturn([$participant->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getUser()->willReturn($user);

        return $participant;
    }

    private function checkMeetingRequestBetween(Sheet $fromSheet, Sheet $toSheet): callable
    {
        return function (Request $meetingRequest) use ($fromSheet, $toSheet) {
            return $meetingRequest->getFromSheet()->getId() === $fromSheet->getId()
                && $meetingRequest->getToSheet()->getId() === $toSheet->getId();
        };
    }
}
