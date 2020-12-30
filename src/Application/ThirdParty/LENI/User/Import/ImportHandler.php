<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\User\Import;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ImportHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        FileStorageInterface $fileStorage,
        SerializerAdapterInterface $serializerAdapter,
        UserRepositoryInterface $userRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->fileStorage = $fileStorage;
        $this->serializerAdapter = $serializerAdapter;
        $this->userRepository = $userRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Import $import): ImportResult
    {
        $importResult = new ImportResult();

        $fileContent = Charset::convertString(
            $this->fileStorage->getContents($import->file),
            $import->charset,
            Charset::UTF_8
        );

        /** @var ImportUserView[] $importUserViews */
        $importUserViews = $this->serializerAdapter->deserialize(
            $fileContent,
            ImportUserView::class.'[]',
            'csv',
            [
                'csv_delimiter' => ';',
                'event' => $import->event,
            ]
        );

        $usersIndexedById = $this->getUsersIndexedById($import->event);
        $leniUserIdsIndexedByUserId = $this->extraDataRepository->getExtraDataForEventIdAndNameIndexedByUserId(
            $import->event->getId(),
            Type::LENI_USER_ID
        );

        foreach ($importUserViews as $importUserView) {
            if (!isset($usersIndexedById[$importUserView->userId])) {
                continue;
            }

            $user = $usersIndexedById[$importUserView->userId];

            if (isset($leniUserIdsIndexedByUserId[$importUserView->userId])) {
                $userExtraData = $leniUserIdsIndexedByUserId[$importUserView->userId];
                $userExtraData->update($importUserView->leniUserId, $this->dateTime);
                $this->extraDataRepository->set($userExtraData);
                $importResult->updatedUser($user);

                continue;
            }

            $this->extraDataRepository->add(
                new ExtraData(
                    $user,
                    $import->event,
                    Type::LENI_USER_ID,
                    $importUserView->leniUserId,
                    $this->dateTime
                )
            );
            $importResult->addedUser($user);
        }

        return $importResult;
    }

    private function getUsersIndexedById(Event $event)
    {
        $usersIndexedById = [];
        $participants = $this->userRepository->findWithEnabledSheetByEvent($event);
        $owners = $this->userRepository->findOwnersWithEnabledSheetByEvent($event);

        $users = array_merge($participants, $owners);

        foreach ($users as $user) {
            $usersIndexedById[$user->getId()] = $user;
        }

        return $usersIndexedById;
    }
}
