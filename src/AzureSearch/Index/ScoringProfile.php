<?php

namespace BenjaminHirsch\Azure\Search\Index;

final class ScoringProfile
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options = [];

    public function __construct(string $name, array $options)
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            "name" => $this->name,
            "text" => [
                "weights" => $this->options["weights"]
            ]
        ];
    }
}