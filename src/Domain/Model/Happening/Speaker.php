<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class Speaker
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var string
     */
    private $organization;

    /**
     * Company logo
     *
     * @var string
     */
    private $logo;

    /**
     * Photo on the speaker
     *
     * @var string
     */
    private $photo;

    /**
     * @var ArrayCollection
     */
    private $talkings;

    /**
     * @var null|User
     */
    private $user;

    /**
     * Speaker constructor.
     *
     * @param Event  $event
     * @param string $firstname
     * @param string $lastname
     * @param string $organization
     * @param string $logo
     * @param string $photo
     * @param null|User $user
     */
    public function __construct(Event $event, $firstname, $lastname, $organization, $logo, $photo, ?User $user)
    {
        $this->event        = $event;
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;
        $this->user         = $user;
        $this->talkings     = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get the company logo of the speaker
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get the photo of the speaker
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Get the user speaker
     *
     * @return null|User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Update speaker.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $organization
     * @param string $logo
     * @param string $photo
     * @param null|User $user
     *
     * @return Speaker
     */
    public function update($firstname, $lastname, $organization, $logo, $photo, ?User $user)
    {
        $this->firstname    = $firstname;
        $this->lastname     = $lastname;
        $this->organization = $organization;
        $this->logo         = $logo;
        $this->photo        = $photo;
        $this->user         = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return ucfirst($this->firstname) . ' ' . mb_strtoupper($this->lastname);
    }

    /**
     * @return Happening[]
     */
    public function getHappenings()
    {
        return $this
            ->talkings
            ->matching(Criteria::create()->orderBy(['position' => 'ASC']))
            ->map(function (Talking $talking) {
                return $talking->getHappening();
            })
            ->toArray();
    }

    /**
     * @param SpeakerTranslation $translation
     */
    public function setTranslation(SpeakerTranslation $translation)
    {
        $this->translations->set($translation->getLocale(), $translation);
    }

    /**
     * @param $locale
     *
     * @return string
     */
    public function getPosition($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getPosition() : '';
    }
}
