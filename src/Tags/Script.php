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
     */
    static public function tagName(): string
    {
        return 'script';
    }

    /**
     * Get path attribute.
     */
    static public function pathAttribute(): string
    {
        return 'src';
    }

    /**
     * Get available attributes.
     */
    static public function availableAttributes(): array
    {
        return [
            'src' => Type::STRING,
            'type' => Type::STRING,
            'charset' => Type::STRING,
            'async' => Type::BOOLEAN,
            'defer' => Type::BOOLEAN,
        ];
    }

    /**
     * Get formatted tag.
     */
    public function tag(): string
    {
        return '<script'.$this->tagAttributes('src', 'type', 'charset', 'async', 'defer').'></script>';
    }
}