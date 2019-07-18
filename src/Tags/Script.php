<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Script
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $src
 * @property bool $async
 * @property bool $defer
 */
class Script extends Tag
{
    /**
     * Get available attributes.
     *
     * @return array
     */
    protected function availableAttributes(): array
    {
        return [
            'src' => static::TYPE_STRING,
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
        return '<script'.$this->tagAttributes('src', 'async', 'defer').'></script>';
    }
}