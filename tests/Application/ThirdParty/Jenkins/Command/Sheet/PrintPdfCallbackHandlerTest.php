<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Jenkins\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet\PrintPdfCallback;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet\PrintPdfCallbackHandler;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\Sheet\PrintPdfErrorException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\PrintPdfMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\PrintPdf\ErrorPrintMail;

class PrintPdfCallbackHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $mailer;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $fileStorage;

    /** @var ObjectProphecy */
    private $fileRepository;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->dateTime = new \DateTime();
        $this->event = $this->prophesize(Event::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->fileRepository = $this->prophesize(FileRepositoryInterface::class);
    }

    public function testHandle()
    {
        $data = [
            'name' => 'trusted_name',
            'display_name' => 'trusted_name',
            'url' =>  'job/trusted_name/',
            'build' =>  [
                'full_url' =>  'http://host/job/trusted_name/10/',
                'number' =>  10,
                'queue_id' =>  116,
                'timestamp' =>  1505481815678,
                'phase' =>  'FINALIZED',
                'status' =>  'SUCCESS',
                'url' =>  'job/trusted_name/10/',
                'scm' =>  [],
                'parameters' =>  [
                    'INPUT' => '/tmp/input/my_file.html',
                    'OUTPUT' => '/tmp/output/my_file.pdf',
                    'EVENTID' => '8',
                    'EMAIL' => 'email@example.net',
                    'LOCALE' => 'fr',
                    'INPUT_FILE_ID' => '14',
                ],
                'log' => '',
                'artifacts' => [],
            ],
        ];

        $this->mailer->send(Argument::that(function (PrintPdfMail $mail) {
            return $mail->event === $this->event->reveal()
                && 'sender@vimeet.dev' === $mail->getSender()
                && $mail->getReceivers() === ['email@example.net']
                && 'fr' === $mail->getLocale()
            ;
        }))->shouldBeCalled();

        $this->eventRepository->getById(8)->shouldBeCalled()->willReturn($this->event->reveal());
        $file  = new File('/tmp/output/my_file.pdf', $this->dateTime);
        $file2 = $this->prophesize(File::class);
        $this->fileRepository->add($file)->shouldBeCalled();
        $this->fileRepository->getById(14)->shouldBeCalled()->willReturn($file2->reveal());
        $this->fileRepository->remove($file2->reveal())->shouldBeCalled();

        $this->fileStorage->remove('/tmp/input/my_file.html', true)->shouldBeCalled();

        $handler = new PrintPdfCallbackHandler(
            $this->fileStorage->reveal(),
            $this->eventRepository->reveal(),
            $this->fileRepository->reveal(),
            $this->mailer->reveal(),
            'sender@vimeet.dev',
            'trusted_name',
            $this->dateTime
        );

        $handler->handle(new PrintPdfCallback($data));
    }

    public function testHandleFailure()
    {
        $this->expectException(PrintPdfErrorException::class);

        $data = [
            'name' => 'trusted_name',
            'display_name' => 'trusted_name',
            'url' =>  'job/trusted_name/',
            'build' =>  [
                'full_url' =>  'http://host/job/trusted_name/10/',
                'number' =>  10,
                'queue_id' =>  116,
                'timestamp' =>  1505481815678,
                'phase' =>  'FINALIZED',
                'status' =>  'FAILURE',
                'url' =>  'job/trusted_name/10/',
                'scm' =>  [],
                'parameters' =>  [
                    'INPUT' => '/tmp/input/my_file.html',
                    'OUTPUT' => '/tmp/output/my_file.pdf',
                    'EVENTID' => '8',
                    'EMAIL' => 'email@example.net',
                    'LOCALE' => 'fr',
                    'INPUT_FILE_ID' => '14',
                ],
                'log' => '',
                'artifacts' => [],
            ],
        ];

        $this->eventRepository->getById(8)->shouldBeCalled()->willReturn($this->event->reveal());

        $this->mailer->send(
            new ErrorPrintMail(
                $this->event->reveal(),
                'sender@vimeet.dev',
                'email@example.net',
                'fr'
            )
        )->shouldBeCalled();

        $handler = new PrintPdfCallbackHandler(
            $this->fileStorage->reveal(),
            $this->eventRepository->reveal(),
            $this->fileRepository->reveal(),
            $this->mailer->reveal(),
            'sender@vimeet.dev',
            'trusted_name',
            $this->dateTime
        );
        $handler->handle(new PrintPdfCallback($data));
    }
}
