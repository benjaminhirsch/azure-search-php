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
        $data = [];

        foreach ($this->options as $option => $value) {
            if ($option === "weights") {
                $data["text"][] = [$option => $value];
            } else {
                $data[$option] = $value;
            }
        }

        return $data;
    }
}