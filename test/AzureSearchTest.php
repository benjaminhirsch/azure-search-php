<?php

namespace B3NTest\Azure\Search;

use B3N\Azure\Search\Exception\UnexpectedValueException;
use B3N\Azure\Search\Index;
use B3N\Azure\Search\Index\Field;
use B3N\Azure\Search\Service;
use PHPUnit\Framework\TestCase;
use Zend\Http\Client;
use Zend\Http\Response;

class AzureSearchTest extends TestCase
{

    /** @var Service */
    private $azure;

    private $client;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->client = $this->getMockBuilder(Client::class)
            ->setMethods(['setMethod', 'send'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->client->method('setMethod')->willReturn($this->client);

        $this->azure = new Service(
            'http://127.0.0.1',
            'AZURE_ADMIN_KEY',
            'AZURE_VERSION',
            null,
            $this->client
        );
    }

    public function testInitAzureAdmin()
    {
        $this->assertInstanceOf('B3N\Azure\Search\Service', $this->azure);
        $this->assertEquals('http://127.0.0.1', $this->azure->getUrl());
        $this->assertEquals('AZURE_ADMIN_KEY', $this->azure->getApiAdminKey());
    }

    public function testCreateIndex()
    {

        $this->client->method('send')->willReturn((new Response())->setStatusCode(201));

        $index = new Index('testindex');
        $index->addField(new Field('test', Field::TYPE_STRING, true))
            ->addField(new Field('test2', Field::TYPE_STRING))
            ->addSuggesters(new Index\Suggest('livesearch', ['test']));

        /** @var \Zend\Http\Response $response */
        $response = $this->azure->createIndex($index);

        $this->assertEquals(Response::STATUS_CODE_201, $response->getStatusCode());
    }

    public function testUploadToIndex()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200));

        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data['value'][] = [
                '@search.action' => Index::ACTION_UPLOAD,
                'test' => uniqid('', true),
                'test2' => microtime() . ' Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy.'
            ];
        }

        /** @var \Zend\Http\Response $response */
        $response = $this->azure->uploadToIndex('testindex', $data);

        $this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
    }

    public function testCountDocuments()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(10));
        $this->assertEquals(10, $this->azure->countDocuments('testindex'));
    }

    public function testFailCountDocuments()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(500)->setContent(null));
        $this->expectException(UnexpectedValueException::class);
        $this->azure->countDocuments('testindex');
    }

    public function testSuggest()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(json_encode(['foo' => 'bar'])));
        $this->assertInstanceOf(
            \stdClass::class,
            $this->azure->suggestions('testindex', uniqid('', true), 'livesearch')
        );
    }

    public function testFailSuggest()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(null));
        $this->expectException(UnexpectedValueException::class);
        $this->assertInstanceOf(
            \stdClass::class,
            $this->azure->suggestions('testindex', uniqid('', true), 'livesearch')
        );
    }

    public function testDeleteIndex()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200));

        /** @var \Zend\Http\Response $response */
        $response = $this->azure->deleteIndex('testindex');

        $this->assertGreaterThanOrEqual(Response::STATUS_CODE_200, $response->getStatusCode());
        $this->assertLessThan(Response::STATUS_CODE_300, $response->getStatusCode());
    }

    public function testSearch()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(json_encode(['foo' => 'bar'])));
        $this->assertInstanceOf(\stdClass::class, $this->azure->search('testindex', 'foo'));
    }

    public function testFailSearch()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(null));
        $this->expectException(UnexpectedValueException::class);
        $this->azure->search('testindex', 'foo');
    }
}
