<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Image
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $src
 * @property string $alt
 * @property string $width
 * @property string $height
 */
class Image extends Tag
{
    /**
     * Get tag name.
     */
    static public function tagName(): string
    {
        return 'img';
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
            'alt' => Type::STRING,
            'width' => Type::STRING,
            'height' => Type::STRING,
        ];
    }

    /**
     * Get formatted tag.
     */
    public function tag(): string
    {
        return '<img'.$this->tagAttributes('src', 'alt', 'width', 'height').' />';
    }
}