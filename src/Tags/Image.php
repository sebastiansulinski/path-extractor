<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Image
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $src
 * @property string $alt
 */
class Image extends Tag
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
            'alt' => static::TYPE_STRING,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<img'.$this->tagAttributes('src', 'alt').' />';
    }
}