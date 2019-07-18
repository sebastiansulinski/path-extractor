<?php

namespace SSD\PathExtractor\Tags;

use InvalidArgumentException;

abstract class Tag
{
    /**
     * String type.
     *
     * @var string
     */
    const TYPE_STRING = 'string';

    /**
     * Boolean type.
     *
     * @var string
     */
    const TYPE_BOOLEAN = 'bool';

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * Tag constructor.
     *
     * @param  array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->setAttributes($attributes);
    }

    /**
     * Set attributes.
     *
     * @param  array $attributes
     * @return void
     */
    private function setAttributes(array $attributes): void
    {
        $this->attributes = $this->availableAttributes();

        array_walk(
            $this->attributes,
            function (string &$type, string $field) use ($attributes) {
                $type = $attributes[$field] ?? null;
            }
        );
    }

    /**
     * Get available attributes.
     *
     * @return array
     */
    abstract protected function availableAttributes(): array;

    /**
     * Get formatted tag.
     *
     * @return string
     */
    abstract public function tag(): string;

    /**
     * Get tag attributes.
     *
     * @param  array|string $attributes
     * @return string
     */
    protected function tagAttributes($attributes): string
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $attributes = array_filter($attributes, function (string $field) {
            return !empty($this->attributes[$field]) &&
                !is_numeric($this->attributes[$field]);
        });

        $attributes = implode(' ', array_map(function ($field) {
            return $this->formatAttribute($field);
        }, $attributes));

        if (empty($attributes)) {
            return '';
        }

        return ' '.$attributes;
    }

    /**
     * Format attribute.
     *
     * @param  string $field
     * @return string
     */
    private function formatAttribute(string $field): string
    {
        if ($this->availableAttributes()[$field] === static::TYPE_BOOLEAN) {
            return $field;
        }

        return $field.'="'.$this->attributes[$field].'"';
    }

    /**
     * Get attribute.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (!isset($this->attributes[$name])) {
            throw new InvalidArgumentException('Invalid attribute for this tag type');
        }

        return $this->attributes[$name];
    }

    /**
     * Get string representation of the object instance.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->tag();
    }
}