<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use InvalidArgumentException;
use OpenTok\Archive;
use OpenTok\ArchiveList;
use OpenTok\Layout;
use OpenTok\MediaMode;
use OpenTok\OpenTok;
use OpenTok\OutputMode;
use OpenTok\Role;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    const DELAY_AFTER_END_TIME = '+15 minutes';
    const SESSION_DEFAULT_OPTIONS = ['mediaMode' => MediaMode::ROUTED];

    /** @var OpenTok */
    private $openTok;

    /** @var string */
    private $tokboxApiKey;

    /** @var bool */
    private $hasSecurity;

    /**
     * @param OpenTok $openTok
     * @param string  $tokboxApiKey
     * @param bool    $hasSecurity
     */
    public function __construct(OpenTok $openTok, string $tokboxApiKey, bool $hasSecurity)
    {
        $this->openTok = $openTok;
        $this->tokboxApiKey = $tokboxApiKey;
        $this->hasSecurity = $hasSecurity;
    }

    /**
     * {@inheritdoc}
     */
    public function createSession(array $options = []): Session
    {
        return $this->openTok->createSession(array_merge(
            self::SESSION_DEFAULT_OPTIONS,
            $options
        ));
    }

    public function archive(string $sessionId, string $name): Archive
    {
        return $this->openTok->startArchive(
            $sessionId,
            [
                'name' => $name,
                'hasVideo' => true,
                'hasAudio' => true,
                'outputMode' => OutputMode::COMPOSED,
                'resolution' => '1280x720',
            ]
        );
    }

    public function changeArchiveLayout(string $archiveId, Layout $layout): void
    {
        $this->openTok->setArchiveLayout($archiveId, $layout);
    }

    public function changeStreamClassList(string $sessionId, string $streamId, string $class): void
    {
        $this->openTok->updateStream(
            $sessionId,
            $streamId,
            [
                'layoutClassList' => [$class]
            ]
        );
    }

    public function stopArchive(string $archiveId): Archive
    {
        return $this->openTok->stopArchive($archiveId);
    }

    public function getArchive(string $archiveId): Archive
    {
        return $this->openTok->getArchive($archiveId);
    }

    public function listArchives(string $sessionId): ArchiveList
    {
        return $this->openTok->listArchives(0, null, $sessionId);
    }

    public function listArchiveUrls(string $sessionId): array
    {
        $list = $this->listArchives($sessionId);
        $urls = [];

        foreach ($list->getItems() as $archive) {
            $url = $archive->url;
            if ($url) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function listArchiveIds(string $sessionId): array
    {
        $list = $this->listArchives($sessionId);
        $archives = $list->getItems();

        usort($archives, static function (Archive $archiveA, Archive $archiveB) {
            return $archiveA->createdAt <=> $archiveB->createdAt;
        });

        $ids = [];
        foreach ($archives as $archive) {
            $ids[] = $archive->id;
        }

        return $ids;
    }

    public function isRecording(string $sessionId): bool
    {
        $existingArchives = $this->listArchives($sessionId);
        $startedArchives = array_filter($existingArchives->getItems(), static function ($archiveItem) {
            return in_array($archiveItem->status, RecordStatus::IS_RECORDING_STATUS, true);
        });

        return 0 < count($startedArchives);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessToken(
        Session $session,
        \DateTimeInterface $endDateTime,
        array $options = [],
        bool $isPublisher = true
    ): string {
        if (true === $this->hasSecurity) {
            $sessionEndDate = new \DateTime($endDateTime->format('Y-m-d H:i:s.u'));

            if (false === $sessionEndDate->modify(self::DELAY_AFTER_END_TIME)) {
                throw new \LogicException('Impossible to modify the date');
            }

            $options['expireTime'] = $sessionEndDate->getTimeStamp();
        }

        $options['role'] = $isPublisher ? Role::PUBLISHER : Role::SUBSCRIBER;

        try {
            return $this->openTok->generateToken($session->getSessionId(), $options);
        } catch (InvalidArgumentException $argumentException) {
            throw new InvalidTokenGeneratorArgumentsException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKey(): string
    {
        return $this->tokboxApiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(string $sessionId): Session
    {
        return new Session($this->openTok, $sessionId);
    }
}
