<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Exception\Import\InvalidEmailException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\MultipleParticipantsFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\ParticipantsOfSameSheetException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\Import\UserNotFoundException;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\MeetingRequest\Import\MeetingRequestRow;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ImportHandler
{
    private FileStorageInterface $fileStorage;
    private SerializerAdapterInterface $serializer;
    private ValidatorInterface $validator;
    private UserRepositoryInterface $userRepository;
    private ParticipantRepositoryInterface $participantRepository;
    private RequestRepositoryInterface $meetingRequestRepository;
    private DateTimeInterface $dateTime;

    public function __construct(
        FileStorageInterface $fileStorage,
        SerializerAdapterInterface $serializer,
        ValidatorInterface $validator,
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        RequestRepositoryInterface $meetingRequestRepository,
        DateTimeInterface $dateTime
    ) {
        $this->fileStorage = $fileStorage;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Import $command)
    {
        $fileContent = Charset::convertString(
            $this->fileStorage->getContents($command->file),
            $command->charset,
            Charset::UTF_8
        );

        /** @var MeetingRequestRow[] $meetingRequestRows */
        $meetingRequestRows = $this->serializer->deserialize(
            $fileContent,
            MeetingRequestRow::class.'[]',
            'csv',
            [
                'csv_delimiter' => ';',
                'event' => $command->event,
            ]
        );

        foreach ($meetingRequestRows as $meetingRequestRow) {
            $this->checkEmail($meetingRequestRow->emailFrom);
            $this->checkEmail($meetingRequestRow->emailTo);

            $fromParticipant = $this->getParticipantFromEmail($meetingRequestRow->emailFrom, $command->event);
            $toParticipant = $this->getParticipantFromEmail($meetingRequestRow->emailTo, $command->event);

            if ($fromParticipant->getSheet()->getId() === $toParticipant->getSheet()->getId()) {
                throw new ParticipantsOfSameSheetException($fromParticipant->getEmail(), $toParticipant->getEmail());
            }

            $existingRequest = $this->meetingRequestRepository->getRequestBetweenSheets($fromParticipant->getSheet(), $toParticipant->getSheet());

            if (null === $existingRequest) {
                // create meeting request if not exist
                $meetingRequest = new Request(
                    $fromParticipant->getSheet(),
                    [$fromParticipant],
                    $toParticipant->getSheet(),
                    [$toParticipant],
                    $this->dateTime,
                    $fromParticipant->getUser(),
                    $command->event
                );
            } else {
                // add participants for existing request
                $meetingRequest = $existingRequest;
                $meetingRequest->setDisabled(false);
                if ($meetingRequest->isSender($fromParticipant->getSheet())) {
                    if (!$meetingRequest->getFromParticipants()->contains($fromParticipant)) {
                        $meetingRequest->addFromParticipant($fromParticipant);
                    }
                    if (!$meetingRequest->getToParticipants()->contains($toParticipant)) {
                        $meetingRequest->addToParticipant($toParticipant);
                    }
                } else {
                    if (!$meetingRequest->getFromParticipants()->contains($toParticipant)) {
                        $meetingRequest->addFromParticipant($toParticipant);
                    }
                    if (!$meetingRequest->getToParticipants()->contains($fromParticipant)) {
                        $meetingRequest->addToParticipant($fromParticipant);
                    }
                }
            }

            if (!$meetingRequest->isApproved()) {
                $meetingRequest->approve($this->dateTime);
            }
            $this->meetingRequestRepository->add($meetingRequest);
        }
    }

    /**
     * @throws InvalidEmailException
     */
    private function checkEmail(string $email): void
    {
        /** @var ConstraintViolationList $emailValidations */
        $emailValidations = $this->validator->validate($email, ValidatorInterface::VALIDATOR_EMAIL_TYPE);
        if ($emailValidations->count() > 0) {
            throw new InvalidEmailException($email);
        }
    }

    /**
     * @throws UserNotFoundException
     * @throws ParticipantNotFoundException
     * @throws MultipleParticipantsFoundException
     */
    private function getParticipantFromEmail(string $email, Event $event): Participant
    {
        $user = $this->userRepository->findByEmail($email);
        if (null === $user) {
            throw new UserNotFoundException($email);
        }
        $participants = $this->participantRepository->getParticipantsByUserForEvent($user, $event);
        if (count($participants) === 0) {
            throw new ParticipantNotFoundException($email);
        }
        if (count($participants) > 1) {
            throw new MultipleParticipantsFoundException($email);
        }

        return $participants[0];
    }
}
