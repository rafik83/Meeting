<?php

namespace Proximum\Vimeet\Tests\Application\Components\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Meeting\RequestImportDenormalizer;
use Proximum\Vimeet\Application\Exception\Import\InvalidKeysException;
use Proximum\Vimeet\Application\View\MeetingRequest\Import\MeetingRequestRow;

class RequestImportDenormalizerTest extends TestCase
{
    private RequestImportDenormalizer $denormalizer;

    public function setup()
    {
        $this->denormalizer = new RequestImportDenormalizer();
    }

    public function testDenormalizeValidFile()
    {
        $this->assertTrue($this->denormalizer->supportsDenormalization([], MeetingRequestRow::class.'[]', 'csv'));

        $result = $this->denormalizer->denormalize($this->getValidCsv(), MeetingRequestRow::class.'[]');

        $expected = [$this->makeRow('user1@example.net', 'user2@example.net')];
        $this->assertEquals($expected, $result);
    }

    public function testThrowExceptionIfInvalidHeaders()
    {

        $this->assertTrue($this->denormalizer->supportsDenormalization([], MeetingRequestRow::class.'[]', 'csv'));

        $this->expectException(InvalidKeysException::class);

        $this->denormalizer->denormalize($this->getInvalidCsv(), MeetingRequestRow::class.'[]');
    }

    private function getValidCsv(): array
    {
        return [
            [
                'Email_asker' => 'user1@example.net',
                'Email_asked' => 'user2@example.net',
            ],
        ];
    }

    private function getInvalidCsv(): array
    {
        return [
            [
                'Email_asker' => 'user1@example.net',
                'Email_asked' => 'user2@example.net',
                'Other_field' => '...',
            ],
            [
                'Email_asker' => 'user1@example.net',
                'Email_asked' => 'user3@example.net',
                'Other_field' => '...',
            ],
        ];
    }

    private function makeRow(string $emailFrom, string $emailTo): MeetingRequestRow
    {
        $row = new MeetingRequestRow();
        $row->emailFrom = $emailFrom;
        $row->emailTo = $emailTo;

        return $row;
    }

}
