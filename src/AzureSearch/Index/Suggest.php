<?php
declare(strict_types=1);

namespace BenjaminHirsch\Azure\Search\Index;

final class Suggest
{

    const MODE_1 = 'analyzingInfixMatching';

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $sourceFields;

    /**
     * @var string
     */
    private $mode;

    /**
     * Suggester constructor.
     *
     * @param string $name The name of the suggester. You use the name of the suggester when
     *                     calling the Suggestions (Azure Search Service REST API).
     * @param array  $sourceFields A list of one or more fields that are the source of the content for suggestions.
     *                     Only fields of type Edm.String and Collection(Edm.String) may be sources for suggestions.
     *                     Only fields that don't have a custom language analyzer set can be used.*
     * @param string $mode The strategy used to search for candidate phrases. The only
     *                     mode currently supported is analyzingInfixMatching, which performs
     *                     flexible matching of phrases at the beginning or in the middle of sentences.
     */
    public function __construct(string $name, array $sourceFields, string $mode = self::MODE_1)
    {
        $this->name = $name;
        $this->sourceFields = $sourceFields;
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'name' => $this->name,
            'sourceFields' => $this->sourceFields,
            'searchMode' => $this->mode
        ];
    }
}
