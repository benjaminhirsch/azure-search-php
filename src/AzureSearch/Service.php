<?php
declare(strict_types=1);

namespace BenjaminHirsch\Azure\Search;

use BenjaminHirsch\Azure\Search\Exception\LengthException;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\AdapterInterface;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client\Adapter\Socket;
use Zend\Http\Header\ContentType;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;

class Service
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $apiAdminKey;

    /**
     * @var string
     */
    private $version;

    /**
     * @var Client
     */
    private $client;

    /**
     * Maximum number of documents per request, this is
     * a Microsoft Azure limitation
     *
     * @var int
     */
    private $batchLimit = 1000;

    /**
     * Search constructor.
     *
     * @param string           $url
     * @param string           $apiAdminKey
     * @param                  $version
     * @param AdapterInterface $adapter
     * @param Client|null      $client
     */
    public function __construct(
        string $url,
        string $apiAdminKey,
        $version,
        AdapterInterface $adapter = null,
        Client $client = null
    ) {
        $this->url = $url;
        $this->apiAdminKey = $apiAdminKey;

        // If curl is installed - use curl, otherwise use php sockets or custom adapter
        if ($adapter === null) {
            $adapter = \function_exists('curl_version') ? new Curl() : new Socket();
        }

        if ($adapter instanceof Curl) {
            $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false)
                ->setCurlOption(CURLOPT_TIMEOUT, 10)
                ->setCurlOption(CURLOPT_RETURNTRANSFER, true);
        }

        $this->version = $version;

        // Setup HTTP Client for the requests
        if (null === $client) {
            $this->client = new Client();
        } else {
            $this->client = $client;
        }
        $this->client->setAdapter($adapter);

        // Set necessary headers / content-type
        $headers = new Headers();
        $headers->addHeader(ContentType::fromString('Content-Type: application/json'))
            ->addHeaderLine('api-key: ' . $apiAdminKey);
        $this->client->setHeaders($headers);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getApiAdminKey(): string
    {
        return $this->apiAdminKey;
    }

    /**
     * You can upload, merge or delete documents from a specified index using HTTP POST. For large
     * numbers of updates, batching of documents (up to 1000 documents per batch, or about
     * 16 MB per batch) is recommended.
     * https://msdn.microsoft.com/en-gb/library/dn798930.aspx
     *
     * @param string $indexName
     * @param array  $data
     *
     * @return Response
     * @throws LengthException
     */
    public function uploadToIndex(string $indexName, array $data): Response
    {
        // Check if max number per request if reached
        if (count($data) > $this->batchLimit) {
            throw new LengthException('Maximum number of 
            documents per request exceeded (max. length' . $this->batchLimit . ').');
        }

        $this->client->setUri($this->url . '/indexes/' . $indexName . '/docs/index?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_POST)
            ->setRawBody(json_encode($data));

        return $this->client->send();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Create a new search index. An index is the primary means of organizing and searching documents
     * in Azure Search, similar to how a table organizes records in a database. Each index has a
     * collection of documents that all conform to the index schema (field names, data types, and
     * properties), but indexes also specify additional constructs (suggesters, scoring profiles,
     * and CORS configuration) that define other search behaviors.
     * https://msdn.microsoft.com/en-gb/library/dn798941.aspx
     *
     * @param Index $index
     *
     * @return Response
     */
    public function createIndex(Index $index): Response
    {
        $this->client->setUri($this->url . '/indexes?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_POST)
            ->setRawBody(json_encode($index()));

        return $this->client->send();
    }

    /**
     * The Delete Index operation removes an index and associated documents from your Azure Search
     * service. You can get the index name from the service dashboard in the Azure Preview portal,
     * or from the API.
     * https://msdn.microsoft.com/en-gb/library/dn798926.aspx
     *
     * @param string $indexName
     *
     * @return Response
     */
    public function deleteIndex(string $indexName): Response
    {
        $this->client->setUri($this->url . '/indexes/' . $indexName . '?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_DELETE);

        return $this->client->send();
    }

    /**
     * Update an existing index
     * https://msdn.microsoft.com/en-gb/library/dn800964.aspx
     *
     * @param Index $index
     *
     * @return Response
     */
    public function updateIndex(Index $index): Response
    {
        $this->client->setUri($this->url . '/indexes/' . $index->getName() . '?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_PUT)
            ->setRawBody(json_encode($index()));

        return $this->client->send();
    }

    /**
     * The List Indexes operation returns a list of the indexes currently in your Azure Search service.
     * https://msdn.microsoft.com/en-gb/library/dn798923.aspx
     *
     * @return array
     */
    public function listIndexes(): array
    {
        $this->client->setUri($this->url . '/indexes?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_GET);
        $response = $this->client->send();

        if ($response->isSuccess()) {
            $obj = json_decode($response->getBody(), true);
            if ($obj !== null) {
                return $obj['value'];
            }
        }

        return [];
    }

    /**
     * Get a specific index by name
     * https://msdn.microsoft.com/en-gb/library/dn798939.aspx
     *
     * @param string $indexName
     *
     * @return array
     */
    public function getIndex(string $indexName): array
    {
        $this->client->setUri($this->url . '/indexes/' . $indexName . '?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_GET);
        $response = $this->client->send();

        $obj = json_decode($response->getBody(), true);
        if ($obj !== null && $response->isSuccess()) {
            return $obj;
        }

        return [];
    }

    /**
     * The Get Index Statistics operation returns from Azure Search a document count for the current
     * index, plus storage usage. You can also get this information from the portal.
     * https://msdn.microsoft.com/en-gb/library/dn798942.aspx
     *
     * @param string $indexName
     *
     * @return array
     */
    public function getIndexStatistics(string $indexName): array
    {
        $this->client->setUri($this->url . '/indexes/' . $indexName . '/stats?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_GET);
        $response = $this->client->send();

        $obj = json_decode($response->getBody(), true);
        if ($obj !== null && $response->isSuccess()) {
            return $obj;
        }

        return [];
    }

    /**
     * Search the documents inside a specific index
     * https://msdn.microsoft.com/en-gb/library/dn798927.aspx
     *
     * @param string      $indexName
     * @param string|null $term
     * @param array|null  $options List of all available options https://msdn.microsoft.com/en-us/library/dn798927.aspx
     *
     * @return array
     */
    public function search(string $indexName, string $term = null, array $options = null): array
    {
        $this->client->setUri($this->url . '/indexes/' . $indexName . '/docs/search?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_POST);

        $args = [];
        if ($term !== null) {
            $args = [
                'search' => $term
            ];
        }

        if ($options !== null) {
            $args = array_merge($args, $options);
        }

        if ($args) {
            $this->client->setRawBody(json_encode($args));
        }

        $response = $this->client->send();

        $obj = json_decode($response->getBody(), true);
        if ($obj !== null && $response->isSuccess()) {
            return $obj;
        }

        return [];
    }

    /**
     * The Suggestions operation retrieves suggestions based on partial search input. It's typically used
     * in search boxes to provide type-ahead suggestions as users are entering search terms.
     * https://msdn.microsoft.com/en-gb/library/dn798936.aspx
     *
     * @param string     $indexName
     * @param string     $term
     * @param string     $suggester
     * @param array|null $options List of all available options https://msdn.microsoft.com/en-us/library/dn798927.aspx
     *
     * @return array
     */
    public function suggestions(string $indexName, string $term, string $suggester, array $options = null): array
    {
        $this->client
            ->setUri($this->url . '/indexes/' . $indexName . '/docs/suggest?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_POST);

        $args = [
            'search' => $term,
            'suggesterName' => $suggester
        ];

        if ($options !== null) {
            $args = array_merge($args, $options);
        }

        if ($args) {
            $this->client->setRawBody(json_encode($args));
        }

        $response = $this->client->send();

        $obj = json_decode($response->getBody(), true);
        if ($obj !== null && $response->isSuccess()) {
            return $obj;
        }

        return [];
    }

    /**
     * The Count Documents operation retrieves a count of the number of documents in a search index.
     * The $count syntax is part of the OData protocol.
     * https://msdn.microsoft.com/en-gb/library/dn798924.aspx
     *
     * @param string $indexName
     *
     * @return int
     */
    public function countDocuments(string $indexName): int
    {
        $this->client->setUri($this->url . '/indexes/' . $indexName . '/docs/$count?api-version=' . $this->getVersion())
            ->setMethod(Request::METHOD_GET);
        $response = $this->client->send();

        if ($response->isSuccess()) {
            return (int)filter_var($response->getBody(), FILTER_SANITIZE_NUMBER_INT);
        }

        return 0;
    }
}
