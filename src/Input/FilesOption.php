<?php

namespace JMOlivas\Phpqa\Input;

class FilesOption
{
    /** @var array */
    private $files;

    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Returns true if this option is provided but has no values
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->files) === 1 && $this->files[0] === null;
    }

    /**
     * Returns true if this option is not provided
     *
     * @return bool
     */
    public function isAbsent()
    {
        return empty($this->files);
    }

    /**
     * Normalize the provided values as an array
     *
     * - If it's is empty, it returns an empty array
     * - If it's a single value separated by commas, it returns the corresponding array
     * - Otherwise returns the value as is.
     *
     * @return array
     */
    public function normalize()
    {
        if ($this->isAbsent() || $this->isEmpty()) {
            return [];
        }
        if (count($this->files) === 1 && strpos($this->files[0], ',') !== false) {
            return explode(',', $this->files[0]);
        }

        return $this->files;
    }
}
