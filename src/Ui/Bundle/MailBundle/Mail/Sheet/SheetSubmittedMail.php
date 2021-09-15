<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetSubmittedMail extends AdminMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.submitted.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/sheetSubmitted.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_VALIDATION_PENDING;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $dateTime;

    /**
     * @var string
     */
    private $sheetName;

    /**
     * Firstname of the user who submitted the sheet
     *
     * @var string
     */
    private $firstname;

    /**
     * Lastname of the user who submitted the sheet
     *
     * @var string
     */
    private $lastname;

    /**
     * @param Event             $event
     * @param string            $sender
     * @param string            $receiver
     * @param string            $locale
     * @param Sheet             $sheet
     * @param Admin             $admin
     * @param DateTimeInterface $dateTime
     * @param string            $sheetName
     * @param string            $firstname
     * @param string            $lastname
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        Sheet $sheet,
        Admin $admin,
        DateTimeInterface $dateTime,
        $sheetName,
        $firstname,
        $lastname
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->admin     = $admin;
        $this->sheet     = $sheet;
        $this->dateTime  = $dateTime;
        $this->sheetName = $sheetName;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%sheetName%' => $this->sheetName,
        ];
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
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
     * @return DateTimeInterface
     */
    public function getDatetime()
    {
        return $this->dateTime;
    }

    /**
     * @return string
     */
    public function getSheetName()
    {
        return $this->sheetName;
    }
}
