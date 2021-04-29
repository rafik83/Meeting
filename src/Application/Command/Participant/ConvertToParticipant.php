<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateData;

class ConvertToParticipant implements Command
{
    /** @var Event */
    public $event;

    /** @var Type */
    public $type;

    /** @var string */
    public $email;

    /** @var string */
    public $locale;

    /** @var array */
    public $dataIndexedByTag;

    /** @var TemplateData */
    public $registrationTemplateData;

    /** @var TemplateData */
    public $sheetTemplateData;

    /** @var null|string */
    public $userEventExtraDataType;

    /** @var string */
    public $sheetState;

    /** @var bool */
    public $toSetInCatalog;

    public function __construct(
        Event $event,
        Type $type,
        string $email,
        string $locale,
        array $dataIndexedByTag,
        TemplateData $registrationTemplateData,
        TemplateData $sheetTemplateData,
        ?string $userEventExtraDataType = null,
        ?string $sheetState = null,
        bool $toSetInCatalog = false
    ) {
        $this->event = $event;
        $this->type = $type;
        $this->email = $email;
        $this->locale = $locale;
        $this->dataIndexedByTag = $dataIndexedByTag;
        $this->registrationTemplateData = $registrationTemplateData;
        $this->sheetTemplateData = $sheetTemplateData;
        $this->userEventExtraDataType = $userEventExtraDataType;
        $this->toSetInCatalog = $toSetInCatalog;

        if (null === $sheetState) {
            $sheetState = Sheet::STATE_PENDING;
        }

        if (!\in_array($sheetState, Sheet::getAllStates(), true)) {
            throw new \InvalidArgumentException('Invalid argument $sheetState; Must be one of Sheet\'s states');
        }

        $this->sheetState = $sheetState;
    }
}
