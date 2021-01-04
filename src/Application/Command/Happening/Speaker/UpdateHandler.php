<?php

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Exception\Speaker\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SpeakerRepositoryInterface
     */
    private $speakerRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorageInterface;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param SpeakerRepositoryInterface $speakerRepository
     * @param FileStorageInterface       $fileStorageInterface
     * @param UserRepositoryInterface    $userRepository
     */
    public function __construct(SpeakerRepositoryInterface $speakerRepository, FileStorageInterface $fileStorageInterface, UserRepositoryInterface $userRepository)
    {
        $this->speakerRepository    = $speakerRepository;
        $this->fileStorageInterface = $fileStorageInterface;
        $this->userRepository       = $userRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $logo  = $update->speaker->getLogo();
        $photo = $update->speaker->getPhoto();

        $userSpeaker = null;
        $emailSpeaker = $update->email;

        if ($emailSpeaker) {
            $userSpeaker = $this->userRepository->findByEventAndEmail($update->speaker->getEvent(), $update->email);
        }

        if ($emailSpeaker !== null && $userSpeaker === null) {
            throw new EmailDoesNotExistException();
        }

        foreach ($update->translations as $locale => $translation) {
            $update->speaker->getTranslations()->get($locale)->update($translation['position']);
        }

        $this->speakerRepository->set($update->speaker->update(
            $update->firstname,
            $update->lastname,
            $update->organization,
            $update->logo ? $this->fileStorageInterface->upload($update->logo) : $logo,
            $update->photo ? $this->fileStorageInterface->upload($update->photo) : $photo,
            $userSpeaker
        ));

        if ($logo !== $update->speaker->getLogo()) {
            $this->fileStorageInterface->remove($logo);
        }

        if ($photo !== $update->speaker->getPhoto()) {
            $this->fileStorageInterface->remove($photo);
        }
    }
}
