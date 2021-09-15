<?php


namespace Proximum\Vimeet\Application\Exception\CallVisio;

class SessionIdAlreadyCreatedException extends CallVisioException
{
    private $existingSessionId;

    public function __construct(string $existingSessionId)
    {
        $this->existingSessionId = $existingSessionId;
    }

    public function getExistingSessionId(): string
    {
        return $this->existingSessionId;
    }
}
