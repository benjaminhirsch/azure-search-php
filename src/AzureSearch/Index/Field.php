<?php
declare(strict_types=1);

namespace BenjaminHirsch\Azure\Search\Index;

final class Field
{
    /**
     * Text that can optionally be tokenized for full-text search (word-breaking,
     * stemming, etc.)
     */
    const TYPE_STRING = "Edm.String";

    /**
     * A list of strings that can optionally be tokenized for full-text search.
     * There is no theoretical upper limit on the number of items in a collection,
     * but the 16 MB upper limit on payload size applies to collection
     */
    const TYPE_COLLECTION = "Collection(Edm.String)";

    /**
     * Contains true/false values.
     */
    const TYPE_BOOLEAN = "Edm.Boolean";

    /**
     * 32-bit integer values.
     */
    const TYPE_INT32 = "Edm.Int32";

    /**
     * 64-bit integer values.
     */
    const TYPE_INT64 = "Edm.Int64";

    /**
     * Double-precision numeric data
     */
    const TYPE_DOUBLE = "Edm.Double";

    /**
     * Date time values represented in the OData V4 format: yyyy-MM-ddTHH:mm:ss.fffZ
     * or yyyy-MM-ddTHH:mm:ss.fff[+|-]HH:mm. Precision of DateTime fields is limited
     * to milliseconds. If you upload datetime values with sub-millisecond precision,
     * the value returned will be rounded up to milliseconds (for example,
     * 2015-04-15T10:30:09.7552052Z will be returned as 2015-04-15T10:30:09.7550000Z).
     */
    const TYPE_DATETIMEOFFSET = "Edm.DateTimeOffset";

    /**
     * A point representing a geographic location on the globe. For request and response
     * bodies the representation of values of this type follows the GeoJSON "Point"
     * type format. For URLs OData uses a literal form based on the WKT standard.
     * A point literal is constructed as geography'POINT(lon lat)'.
     */
    const TYPE_GEOGRAPHYPOINT = "Edm.GeographyPoint";

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $searchable;

    /**
     * @var bool
     */
    private $filterable;

    /**
     * @var bool
     */
    private $sortable;

    /**
     * @var bool
     */
    private $facetable;

    /**
     * @var bool
     */
    private $key;

    /**
     * @var bool
     */
    private $retrievable;

    /**
     * @var string
     */
    private $analyzer;

    /**
     * Field constructor.
     *
     * @param string $name Sets the name of the field.
     * @param string $type Sets the data type for the field.
     * @param bool   $key Marks the field as containing unique identifiers for documents within the index.
     *                  Exactly one field must be chosen as the key field and it must be of type Edm.String.
     *                  Key fields can be used to look up documents directly.
     * @param bool   $searchable Marks the field as full-text search-able. This means it
     *                         will undergo analysis such as word-breaking during indexing.
     *                         If you set a searchable field to a value like "sunny day",
     *                         internally it will be split into the individual tokens "sunny"
     *                         and "day". This enables full-text searches for these terms. Fields
     *                         of type Edm.String or Collection(Edm.String) are searchable by default.
     *                         Fields of other types are not searchable.
     * @param bool   $filterable Allows the field to be referenced in $filter queries. filterable differs
     *                         from searchable in how strings are handled. Fields of type Edm.String or
     *                         Collection(Edm.String) that are filterable do not undergo word-breaking,
     *                         so comparisons are for exact matches only. For example, if you set such a
     *                         field f to "sunny day", $filter=f eq 'sunny' will find no matches, but $filter=f eq
     *                         'sunny day' will. All fields are filterable by default.
     * @param bool   $sortable By default the system sorts results by score, but in many experiences users will
     *                       want to sort by fields in the documents. Fields of type Collection(Edm.String)
     *                       cannot be sortable. All other fields are sortable by default.
     * @param bool   $facetable Typically used in a presentation of search results that includes hit count by
     *                        category (e.g. search for digital cameras and see hits by brand, by megapixels,
     *                        by price, etc.). This option cannot be used with fields of type Edm.GeographyPoint.
     *                        All other fields are facetable by default.
     * @param bool   $retrievable Sets whether the field can be returned in a search result. This is useful
     *                          when you want to use a field (e.g., margin) as a filter, sorting, or scoring
     *                          mechanism but do not want the field to be visible to the end user. This
     *                          attribute must be true for key fields.
     * @param string $analyzer Sets the name of the language analyzer to use for the field
     */
    public function __construct(
        string $name,
        string $type,
        bool $key = false,
        bool $searchable = true,
        bool $filterable = true,
        bool $sortable = true,
        bool $facetable = true,
        bool $retrievable = true,
        string $analyzer = null
    ) {

        $this->name = $name;
        $this->type = $type;
        $this->searchable = $searchable;
        $this->filterable = $filterable;
        $this->sortable = $sortable;
        $this->facetable = $facetable;
        $this->key = $key;
        $this->retrievable = $retrievable;
        $this->analyzer = $analyzer;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'sortable' => $this->sortable,
            'facetable' => $this->facetable,
            'key' => $this->key,
            'retrievable' => $this->retrievable,
            'analyzer' => $this->analyzer
        ];
    }
}
