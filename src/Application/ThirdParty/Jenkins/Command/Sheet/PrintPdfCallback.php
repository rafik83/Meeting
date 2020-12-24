<?php

namespace Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet;

use Proximum\Vimeet\Application\ThirdParty\Jenkins\AbstractSetStatus;

class PrintPdfCallback extends AbstractSetStatus
{
    /** @var string */
    public $input;

    /** @var string */
    public $output;

    /** @var int */
    public $eventId;

    /** @var string */
    public $email;

    /** @var string */
    public $locale;

    /** @var int */
    public $inputFileId;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (!isset($data['build']['parameters']['INPUT'])
            || !isset($data['build']['parameters']['OUTPUT'])
            || !isset($data['build']['parameters']['EVENTID'])
            || !isset($data['build']['parameters']['EMAIL'])
            || !isset($data['build']['parameters']['LOCALE'])
            || !isset($data['build']['parameters']['INPUT_FILE_ID'])
        ) {
            throw new \DomainException(
                sprintf("Missing parameters:\n
                    input: %s,\n
                    output: %s,\n
                    eventId: %s,\n
                    email: %s,\n
                    locale: %s,\n
                    inputFileId: %s
                ",
                    $data['build']['parameters']['INPUT'] ?? '',
                    $data['build']['parameters']['OUTPUT'] ?? '',
                    $data['build']['parameters']['EVENTID'] ?? '',
                    $data['build']['parameters']['EMAIL'] ?? '',
                    $data['build']['parameters']['LOCALE'] ?? '',
                    $data['build']['parameters']['INPUT_FILE_ID'] ?? ''
                )
            );
        }

        $this->input       = $data['build']['parameters']['INPUT'];
        $this->output      = $data['build']['parameters']['OUTPUT'];
        $this->eventId     = (int) $data['build']['parameters']['EVENTID'];
        $this->email       = $data['build']['parameters']['EMAIL'];
        $this->locale      = $data['build']['parameters']['LOCALE'];
        $this->inputFileId = (int) $data['build']['parameters']['INPUT_FILE_ID'];
    }
}
