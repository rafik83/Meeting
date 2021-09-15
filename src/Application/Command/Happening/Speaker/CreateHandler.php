<?php

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Exception\Speaker\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class CreateHandler
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
     * CreateHandler constructor.
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $userSpeaker = null;
        $emailSpeaker = $create->email;

        if ($emailSpeaker) {
            $userSpeaker = $this->userRepository->findByEventAndEmail($create->event, $create->email);
        }

        if ($emailSpeaker !== null && $userSpeaker === null) {
            throw new EmailDoesNotExistException();
        }

        $speaker = new Speaker(
            $create->event,
            $create->firstname,
            $create->lastname,
            $create->organization,
            $this->fileStorageInterface->upload($create->logo),
            $this->fileStorageInterface->upload($create->photo),
            $userSpeaker
        );

        foreach ($create->translations as $locale => $translation) {
            $speaker->getTranslations()->set(
                $locale,
                new SpeakerTranslation($speaker, $locale, $translation['position'])
            );
        }

        $this->speakerRepository->add($speaker);
    }
}
