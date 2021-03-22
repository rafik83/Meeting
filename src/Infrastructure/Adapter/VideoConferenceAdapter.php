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
use OpenTok\Stream;
use OpenTok\StreamList;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Domain\Happening\Webinar\Broadcast\Broadcast as DomainBroadcast;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
use Proximum\Vimeet\Infrastructure\Tokbox\Broadcast\Broadcast;
use Proximum\Vimeet\Infrastructure\Tokbox\Client;
use stdClass;

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    public const DELAY_AFTER_END_TIME = 15 * 60;
    public const SESSION_DEFAULT_OPTIONS = ['mediaMode' => MediaMode::ROUTED];
    public const MAX_ARCHIVE_NAME_LENGTH = 255;

    /** @var OpenTok */
    private $openTok;

    /** @var string */
    private $tokboxApiKey;

    /** @var bool */
    private $hasSecurity;

    /** @var Client */
    private $tokboxClient;

    /**
     * @param OpenTok $openTok
     * @param string  $tokboxApiKey
     * @param bool    $hasSecurity
     */
    public function __construct(
        OpenTok $openTok,
        string $tokboxApiKey,
        bool $hasSecurity,
        Client $tokboxClient
    ) {
        $this->openTok = $openTok;
        $this->tokboxApiKey = $tokboxApiKey;
        $this->hasSecurity = $hasSecurity;
        $this->tokboxClient = $tokboxClient;
    }

    public function checkApiKey(string $apiKey): bool {
        return !empty($apiKey) && $apiKey === $this->tokboxApiKey;
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

    public function getSessionStreamCount($sessionId): int
    {
        /** @var StreamList */
        $streamList = $this->openTok->listStreams($sessionId);

        return $streamList->totalCount();
    }

    public function archive(string $sessionId, string $name): Archive
    {
        return $this->openTok->startArchive(
            $sessionId,
            [
                'name' => mb_substr($name, 0, self::MAX_ARCHIVE_NAME_LENGTH),
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

    public function changeArchiveToVertical(string $archiveId): void
    {
        $this->openTok->setArchiveLayout($archiveId, Layout::getVerticalPresentation());
    }

    public function changeArchiveToBestFit(string $archiveId): void
    {
        $this->openTok->setArchiveLayout($archiveId, Layout::getBestFit());
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

    public function changeArchiveLayoutAuto(string $sessionId, string $archiveId): void
    {
        /** @var StreamList $streamList */
        $streamList = $this->openTok->listStreams($sessionId);

        /** @var Stream $stream */
        foreach ($streamList->getItems() as $stream) {
            if ($stream->videoType !== 'camera') {
                $this->changeStreamClassList($sessionId, $stream->id, 'focus');
                $this->changeArchiveToVertical($archiveId);
                return;
            }
        }
    }

    public function changeBroadcastFocus(DomainBroadcast $broadcast, string $streamId): void
    {
        $this->changeStreamClassList($broadcast->getSessionId(), $streamId, 'focus');
        $this->openTok->updateBroadcastLayout($broadcast->getBroadcastId(), Layout::getVerticalPresentation());
    }

    public function resetBroadcastFocus(string $broadcastId): void
    {
        $this->openTok->updateBroadcastLayout($broadcastId, Layout::getBestFit());
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
            $options['expireTime'] = $endDateTime->getTimeStamp() + self::DELAY_AFTER_END_TIME;
        }

        $options['role'] = $isPublisher ? Role::PUBLISHER : Role::SUBSCRIBER;

        try {
            return $this->openTok->generateToken($session->getSessionId(), $options);
        } catch (InvalidArgumentException $argumentException) {
            throw new InvalidTokenGeneratorArgumentsException(
                'Failed to create token for session ' . $session->getSessionId() . PHP_EOL . $argumentException->getMessage(),
                0,
                $argumentException
            );
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

    public function startBroadcast(
        string $sessionId,
        int $duration
    ): DomainBroadcast {
        $broadcast = Broadcast::createFromTokboxObject(
            $this->openTok->startBroadcast($sessionId, [
                'maxDuration' => $duration,
                'resolution' => '1280x720',
                'outputs' => ['hls' => new stdClass()],
            ])
        );

        return $broadcast;
    }

    public function stopBroadcast(string $broadcastId): DomainBroadcast
    {
        return Broadcast::createFromTokboxObject($this->openTok->stopBroadcast($broadcastId));
    }

    public function getBroadcastForSession(string $session): ?DomainBroadcast
    {
        $json = $this->tokboxClient->getBroadcastForSession($session);

        if (empty($json)) {
            return null;
        }

        return Broadcast::createFromJson($json);
    }

    public function getBroadcastsForSession(string $session): array
    {
        $list = $this->tokboxClient->getBroadcastsForSession($session);

        if (empty($list)) {
            return [];
        }

        $broadcasts = [];
        foreach ($list as $item) {
            $broadcasts[] = Broadcast::createFromJson($item);
        }

        return $broadcasts;
    }
}
