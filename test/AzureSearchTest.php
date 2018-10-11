<?php
declare(strict_types=1);

namespace BenjaminHirschTest\Azure\Search;

use BenjaminHirsch\Azure\Search\Exception\LengthException;
use BenjaminHirsch\Azure\Search\Index;
use BenjaminHirsch\Azure\Search\Index\Field;
use BenjaminHirsch\Azure\Search\Service;
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

    public function testConstruct()
    {
        $this->assertInstanceOf(Service::class, $foo = new Service(
            'http://127.0.0.1',
            'AZURE_ADMIN_KEY',
            'AZURE_VERSION',
            null,
            $this->client
        ));

        $this->client->method('send')->willReturn((new Response())->setStatusCode(200));
        $foo->getIndex('testindex');

        $this->assertInstanceOf(Service::class, $foo = new Service(
            'http://127.0.0.1',
            'AZURE_ADMIN_KEY',
            'AZURE_VERSION',
            null,
            null
        ));

        $this->expectException(\Zend\Http\Exception\RuntimeException::class);
        $foo->getIndex('testindex');
    }
    
    public function testInitAzureAdmin()
    {
        $this->assertInstanceOf('BenjaminHirsch\Azure\Search\Service', $this->azure);
        $this->assertEquals('http://127.0.0.1', $this->azure->getUrl());
        $this->assertEquals('AZURE_ADMIN_KEY', $this->azure->getApiAdminKey());
    }

    public function testCreateIndex()
    {

        $this->client->method('send')->willReturn((new Response())->setStatusCode(201));

        $index = new Index('testindex');
        $index->addField(new Field('test', Field::TYPE_STRING, true))
            ->addField(new Field('test2', Field::TYPE_STRING))
            ->addCrossOrigins('foo', 1)
            ->addSuggesters(new Index\Suggest('livesearch', ['test']));

        /** @var \Zend\Http\Response $response */
        $response = $this->azure->createIndex($index);

        $this->assertEquals(Response::STATUS_CODE_201, $response->getStatusCode());
    }
    
    public function testAddScoringProfile()
    {
        $this->expectException(\RuntimeException::class);
        $index = new Index('testindex');
        $index->addScoringProfile();
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
        $this->assertEquals(0, $this->azure->countDocuments('testindex'));
    }

    public function testSuggest()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(json_encode(['foo' => 'bar'])));
        $this->assertEquals(
            ['foo' => 'bar'],
            $this->azure->suggestions('testindex', uniqid('', true), 'livesearch', ['foo' => 'bar'])
        );
    }

    public function testFailSuggest()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(null));
        $this->assertEquals(
            [],
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
        $this->assertEquals(['foo' => 'bar'], $this->azure->search('testindex', 'foo', ['foo' => 'bar']));
    }

    public function testFailSearch()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(null));

        $this->assertEquals([], $this->azure->search('testindex', 'foo'));
    }

    public function testUpdateIndex()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200));
        $response = $this->azure->updateIndex(new Index('testindex'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
    }

    public function testListIndexes()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(json_encode([
                'value' => [
                    'foo' => 'bar'
                ]
            ])));

        $this->assertArrayHasKey(
            'foo',
            $this->azure->listIndexes()
        );
    }

    public function testFailListIndexes()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)
            ->setContent(json_encode(null)));

        $this->assertEquals(
            [],
            $this->azure->listIndexes()
        );
    }

    public function testGetIndex()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(
            json_encode(['foo' => 'bar'])
        ));
        $this->assertEquals(['foo' => 'bar'], $this->azure->getIndex('testindex'));
    }

    public function testFailGetIndex()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(null));
        $this->assertEquals([], $this->azure->getIndex('testindex'));
    }

    public function testGetIndexStatistics()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(
            json_encode(['foo' => 'bar'])
        ));
        $this->assertEquals(['foo' => 'bar'], $this->azure->getIndexStatistics('testindex'));
    }

    public function testFailGetIndexStatistics()
    {
        $this->client->method('send')->willReturn((new Response())->setStatusCode(200)->setContent(null));
        $this->assertEquals([], $this->azure->getIndexStatistics('testindex'));
    }
    
    public function testExceededUploadLimit()
    {
        $this->expectException(LengthException::class);
        $this->azure->uploadToIndex('testindex', range(1, 2000));
    }
}
