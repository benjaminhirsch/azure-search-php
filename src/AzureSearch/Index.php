<?php
declare(strict_types=1);

namespace BenjaminHirsch\Azure\Search;

use BenjaminHirsch\Azure\Search\Index\Field;
use BenjaminHirsch\Azure\Search\Index\Suggest;

final class Index
{

    /**
     * An upload action is similar to an "upsert" where the document will be inserted if it is new and
     * updated/replaced if it exists. Note that all fields are replaced in the update case.
     */
    const ACTION_UPLOAD = 'upload';

    /**
     * Merge updates an existing document with the specified fields. If the document doesn't exist, the merge
     * will fail. Any field you specify in a merge will replace the existing field in the document. This
     * includes fields of type Collection(Edm.String). For example, if the document contains a field "tags"
     * with value ["budget"] and you execute a merge with value ["economy", "pool"] for "tags", the final
     * value of the "tags" field will be ["economy", "pool"]. It will not be ["budget", "economy", "pool"].
     */
    const ACTION_MERGE = 'merge';

    /**
     * This action behaves like merge if a document with the given key already exists in the index. If the
     * document does not exist, it behaves like upload with a new document.
     */
    const ACTION_MERGE_OR_UPLOAD = 'mergeOrUpload';

    /**
     * Delete removes the specified document from the index. Note that any field you specify in a delete
     * operation, other than the key field, will be ignored. If you want to remove an individual field
     * from a document, use merge instead and simply set the field explicitly to null.
     */
    const ACTION_DELETE = 'delete';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $suggesters = [];

    /**
     * @var mixed
     */
    private $crossOrigins;

    /**
     * @var int
     */
    private $maxAgeInSecond;

    /**
     * @var string
     */
    private $name;

    /**
     * Index constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Field $field
     *
     * @return Index
     */
    public function addField(Field $field): Index
    {
        $this->fields[] = $field();

        return $this;
    }

    /**
     * @param Suggest $suggest
     *
     * @return Index
     */
    public function addSuggesters(Suggest $suggest): Index
    {
        $this->suggesters[] = $suggest();

        return $this;
    }

    /**
     * @param mixed(string|array) $allowedOrigins
     * @param int $maxAgeInSeconds
     *
     * @return Index
     */
    public function addCrossOrigins($allowedOrigins, int $maxAgeInSeconds = 0): Index
    {
        $this->crossOrigins = $allowedOrigins;
        $this->maxAgeInSecond = $maxAgeInSeconds;

        return $this;
    }

    /**
     * @todo Implement possibility to add scoring profiles
     *
     * @throws \Exception
     */
    public function addScoringProfile()
    {
        throw new \RuntimeException('Not yet implemented');
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'name' => $this->name,
            'fields' => $this->fields,
            'suggesters' => $this->suggesters,
            'corsOptions' => $this->crossOrigins
        ];
    }
}
