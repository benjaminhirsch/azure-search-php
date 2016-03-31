<?php

namespace B3NTest\Azure;

@include dirname(__FILE__) . '/config.php';

use B3N\Azure\Index;
use B3N\Azure\Index\Field;
use B3N\Azure\Search;
use Zend\Http\Response;

class AzureSearchAdminTest extends \PHPUnit_Framework_TestCase
{

    /** @var Search */
    private $azure;

    public function __construct()
    {
        if (!defined('AZURE_URL') || !defined('AZURE_ADMIN_KEY') || !defined('AZURE_VERSION')) {
            throw new \Exception('Constant AZURE_URL or AZURE_ADMIN_KEY or AZURE_VERSION isnt\'t set!');
        }

        $this->azure = new Search(AZURE_URL, AZURE_ADMIN_KEY, AZURE_VERSION);
    }

    public function testInitAzureAdmin()
    {
        $this->assertInstanceOf('B3N\Azure\Search', $this->azure);
        $this->assertEquals(AZURE_URL, $this->azure->getUrl());
        $this->assertEquals(AZURE_ADMIN_KEY, $this->azure->getApiAdminKey());
    }

    public function testCreateIndex()
    {
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
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data['value'][] = [
                '@search.action' => Index::ACTION_UPLOAD,
                'test' => uniqid(),
                'test2' => microtime() . ' Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy.'
            ];
        }

        /** @var \Zend\Http\Response $response */
        $response = $this->azure->uploadToIndex('testindex', $data);

        $this->assertEquals(Response::STATUS_CODE_200, $response->getStatusCode());
    }

    public function testCountDocuments()
    {
        // Wait a bit for Azure to compute
        sleep(5);
        $this->assertEquals(10, $this->azure->countDocuments('testindex'));
    }

    public function testSuggest()
    {
        $this->assertInstanceOf(\stdClass::class, $this->azure->suggestions('testindex', uniqid(), 'livesearch'));
    }

    public function testDeleteIndex()
    {
        /** @var \Zend\Http\Response $response */
        $response = $this->azure->deleteIndex('testindex');

        $this->assertGreaterThanOrEqual(Response::STATUS_CODE_200, $response->getStatusCode());
        $this->assertLessThan(Response::STATUS_CODE_300, $response->getStatusCode());
    }
}
