<?php

namespace Proximum\Vimeet\Application\View\Speaker;

abstract class AbstractSpeakerView
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $picture;

    /**
     * @var string
     */
    private $companyPicture;

    /**
     * HappeningSpeakerView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $picture
     * @param string $companyPicture
     */
    public function __construct(
        $firstname,
        $lastname,
        $position,
        $picture,
        $companyPicture
    ) {
        $this->firstname      = $firstname;
        $this->lastname       = $lastname;
        $this->position       = $position;
        $this->picture        = $picture;
        $this->companyPicture = $companyPicture;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return bool
     */
    public function hasPicture()
    {
        return !empty($this->picture);
    }

    /**
     * @return bool
     */
    public function hasCompanyPicture()
    {
        return !empty($this->companyPicture);
    }

    /**
     * @return string
     */
    public function getCompanyPicture()
    {
        return $this->companyPicture;
    }

    /**
     * @return string
     */
    public function getInitials()
    {
        return sprintf(
            '%s%s',
            mb_strtoupper(mb_substr($this->firstname, 0, 1)),
            mb_strtoupper(mb_substr($this->lastname, 0, 1))
        );
    }
}
