<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Api;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniApiCallerTest extends TestCase
{
    private $event;
    private $httpAdapter;
    private $extraParameterRepository;
    private $dateTime;

    public function setUp()
    {
        $this->dateTime = new \DateTime();
        $this->event = $this->prophesize(Event::class);
        $this->httpAdapter = $this->prophesize(HttpAdapterInterface::class);

        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_USER)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_USER,
                    'user',
                    'leni_user',
                    $this->dateTime
                )
            )
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_EVENT)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_EVENT,
                    'event',
                    'leni_event',
                    $this->dateTime
                )
            )
        ;
    }

    private function getParametersForSave()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_SAVE_ENDPOINT)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_SAVE_ENDPOINT,
                    'endpoint',
                    'https://endpoint.leni.save',
                    $this->dateTime
                )
            )
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_MODE)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_MODE,
                    'mode',
                    'save',
                    $this->dateTime
                )
            )
        ;
    }

    public function testValidSave()
    {
        $this->getParametersForSave();

        $body = json_encode(
            [
                'idEvt' => 'leni_event',
                'idUser' => 'leni_user',
                'mode' => LeniConstants::LENI_MODE,
                'app' => LeniConstants::LENI_APP,
                'data' => ['whatever' => 'value'],
            ]
        );

        $this->httpAdapter
            ->post(
                'https://endpoint.leni.save',
                [
                    'Authorization' => 'Basic ' . base64_encode('leni_user'),
                    'Host' => LeniConstants::LENI_HOST,
                    'Content-Type' => 'application/json',
                    'Content-Length' => mb_strlen($body),
                    'Connection' => 'Close',
                ],
                $body
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200, '{"IsValid": true}'))
        ;

        $leniApiCaller = new LeniApiCaller($this->httpAdapter->reveal(), $this->extraParameterRepository->reveal());
        $response = $leniApiCaller->save($this->event->reveal(), ['whatever' => 'value']);

        $this->assertEquals(['IsValid' => true], $response);
    }

    public function testInvalidSave()
    {
        $this->expectException(NotValidApiCallException::class);

        $this->getParametersForSave();

        $body = json_encode(
            [
                'idEvt' => 'leni_event',
                'idUser' => 'leni_user',
                'mode' => LeniConstants::LENI_MODE,
                'app' => LeniConstants::LENI_APP,
                'data' => ['whatever' => 'value'],
            ]
        );

        $this->httpAdapter
            ->post(
                'https://endpoint.leni.save',
                [
                    'Authorization' => 'Basic ' . base64_encode('leni_user'),
                    'Host' => LeniConstants::LENI_HOST,
                    'Content-Type' => 'application/json',
                    'Content-Length' => mb_strlen($body),
                    'Connection' => 'Close',
                ],
                $body
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200, '{"IsValid": false}'))
        ;

        $leniApiCaller = new LeniApiCaller($this->httpAdapter->reveal(), $this->extraParameterRepository->reveal());
        $leniApiCaller->save($this->event->reveal(), ['whatever' => 'value']);
    }

    private function getParametersForGet()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_GET_ENDPOINT)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_GET_ENDPOINT,
                    'endpoint',
                    'https://endpoint.leni.get',
                    $this->dateTime
                )
            )
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_MODE)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_MODE,
                    'mode',
                    'save',
                    $this->dateTime
                )
            )
        ;
    }

    public function testGet()
    {
        $this->getParametersForGet();

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_MODE)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_LENI_MODE,
                    'mode',
                    'get',
                    $this->dateTime
                )
            )
        ;

        $body = json_encode(
            [
                'idEvt' => 'leni_event',
                'filters' => ['myfilter' => 'value'],
                'fields' => ['field1', 'field2', 'field3'],
                'start' => 0,
                'take' => 1,
                'sort' => ['field1' => 'asc'],
            ]
        );

        $this->httpAdapter
            ->post(
                'https://endpoint.leni.get',
                [
                    'Authorization' => 'Basic ' . base64_encode('leni_user'),
                    'Host' => LeniConstants::LENI_HOST,
                    'Content-Type' => 'application/json',
                    'Content-Length' => mb_strlen($body),
                    'Connection' => 'Close',
                ],
                $body
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200, '{"results": {"whatever": "data"}}'))
        ;

        $leniApiCaller = new LeniApiCaller($this->httpAdapter->reveal(), $this->extraParameterRepository->reveal());
        $response = $leniApiCaller->get(
            $this->event->reveal(),
            [
                'field1',
                'field2',
                'field3',
            ],
            ['myfilter' => 'value'],
            ['field1' => 'asc'],
            0,
            1
        );

        $this->assertEquals(['whatever' => 'data'], $response);
    }
}
