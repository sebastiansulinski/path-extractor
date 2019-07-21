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
     *
     * @return string
     */
    static public function tagName(): string
    {
        return 'img';
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
            'alt' => static::TYPE_STRING,
            'width' => static::TYPE_STRING,
            'height' => static::TYPE_STRING,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<img'.$this->tagAttributes('src', 'alt', 'width', 'height').' />';
    }
}