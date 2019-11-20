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
        $data = ["name" => $this->name];

        foreach ($this->options as $key => $value) {
            if ($key === "weights") {
                $data["text"] = [$key => $value];
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}