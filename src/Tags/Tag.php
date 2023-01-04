<?php

namespace SSD\PathExtractor\Tags;

use InvalidArgumentException;

abstract class Tag
{
    private array $attributes;

    /**
     * Tag constructor.
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get tag name.
     */
    abstract static public function tagName(): string;

    /**
     * Get path attribute.
     */
    abstract static public function pathAttribute(): string;

    /**
     * Get available attributes.
     */
    abstract static public function availableAttributes(): array;

    /**
     * Get formatted tag.
     */
    abstract public function tag(): string;

    /**
     * Get path.
     */
    public function path(): string
    {
        return $this->{static::pathAttribute()};
    }

    /**
     * Get tag attributes.
     */
    protected function tagAttributes(array|string $attributes): string
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $attributes = array_filter($attributes, function (string $field) {
            return !empty($this->attributes[$field]);
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
     */
    private function formatAttribute(string $field): string
    {
        if ($this->availableAttributes()[$field] === Type::BOOLEAN) {
            return $field;
        }

        return $field.'="'.$this->attributes[$field].'"';
    }

    /**
     * Get attribute using magic method.
     */
    public function __get(string $name): mixed
    {
        if (!isset($this->attributes[$name])) {
            throw new InvalidArgumentException('Invalid attribute for this tag type');
        }

        return $this->attributes[$name];
    }

    /**
     * Get string representation of the object instance.
     */
    public function __toString(): string
    {
        return $this->tag();
    }

    /**
     * Get array representation of the object instance.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}