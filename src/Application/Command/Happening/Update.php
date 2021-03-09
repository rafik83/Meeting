<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class Update extends AbstractHappeningCommand
{
    /** @var Happening */
    public $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
        $this->category = $happening->getCategory();
        $this->begin = $happening->getBegin();
        $this->end = $happening->getEnd();
        $this->questionAllowed = $happening->isQuestionAllowed();
        $this->limitParticipant = $happening->getLimitParticipant();
        $this->invitationCode = $happening->getInvitationCode();
        $this->liveUrl = $happening->getLiveUrl();
        $this->sidebarAllowed = $happening->isSidebarAllowed();
        $this->webinarRecorded = $happening->isWebinarRecorded();
        $this->webinarRecordSentToSpeakers = $happening->isWebinarRecordSentToSpeakers();
        $this->allowHls = $happening->allowWebinarOnHLS();
        $this->mustEvaluateHappening = $happening->mustEvaluateHappening();

        if ($happening->isWebinar()) {
            $this->happeningType = self::TYPE_WEBINAR;
        }

        if ($happening->isInteractiveWebinar()) {
            $this->happeningType = self::TYPE_WEBINAR_INTERACTIVE;
        }

        if ($happening->isVideoWebinar()) {
            $this->happeningType = self::TYPE_WEBINAR_VIDEO;
        }

        foreach ($happening->getEvent()->getLocales() as $locale) {
            if ($happening->getTranslations()->containsKey($locale)) {
                /** @var Happening\HappeningTranslation $translation */
                $translation = $happening->getTranslations()->get($locale);

                $this->translations[$locale] = [
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                    'currentWebinarHeaderImage' => $translation->getWebinarHeaderImage(),
                    'currentWebinarWaitingMediaFile' => $translation->getWebinarWaitingMediaFile(),
                    'currentWebinarWaitingMediaType' => $translation->getWebinarWaitingMediaType(),
                    'webinarHeaderImage' => null,
                    'webinarWaitingMedia' => null,
                ];
            } else {
                $this->translations[$locale] = [
                    'title' => '',
                    'description' => '',
                    'currentWebinarHeaderImage' => null,
                    'currentWebinarWaitingMediaFile' => null,
                    'currentWebinarWaitingMediaType' => null,
                    'webinarHeaderImage' => null,
                    'webinarWaitingMedia' => null,
                ];
            }
        }

        foreach ($happening->getSpeakers() as $position => $speaker) {
            $this->talkings[] = [
                'speaker' => $speaker,
                'position' => $position,
            ];
        }

        $this->types = $happening->getTypes();
    }
}
