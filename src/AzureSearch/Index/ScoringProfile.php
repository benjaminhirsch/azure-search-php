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
    private $text = [];

    public function __construct(string $name, array $weights)
    {
        $this->name = $name;
        $this->text["weights"] = $weights;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            "name" => $this->name,
            "text" => $this->text
        ];
    }
}