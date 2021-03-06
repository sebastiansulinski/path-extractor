<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Script
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $src
 * @property string $type
 * @property string $charset
 * @property bool $async
 * @property bool $defer
 */
class Script extends Tag
{
    /**
     * Get tag name.
     *
     * @return string
     */
    static public function tagName(): string
    {
        return 'script';
    }

    /**
     * Get path attribute.
     *
     * @return string
     */
    static public function pathAttribute(): string
    {
        return 'src';
    }

    /**
     * Get available attributes.
     *
     * @return array
     */
    static public function availableAttributes(): array
    {
        return [
            'src' => static::TYPE_STRING,
            'type' => static::TYPE_STRING,
            'charset' => static::TYPE_STRING,
            'async' => static::TYPE_BOOLEAN,
            'defer' => static::TYPE_BOOLEAN,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<script'.$this->tagAttributes('src', 'type', 'charset', 'async', 'defer').'></script>';
    }
}