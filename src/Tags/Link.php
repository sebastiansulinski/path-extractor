<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Link
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $href
 * @property string $type
 * @property string $rel
 */
class Link extends Tag
{
    /**
     * Get tag name.
     *
     * @return string
     */
    static public function tagName(): string
    {
        return 'link';
    }

    /**
     * Get path attribute.
     *
     * @return string
     */
    static public function pathAttribute(): string
    {
        return 'href';
    }

    /**
     * Get available attributes.
     *
     * @return array
     */
    static public function availableAttributes(): array
    {
        return [
            'href' => static::TYPE_STRING,
            'type' => static::TYPE_STRING,
            'rel' => static::TYPE_STRING,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<link'.$this->tagAttributes('href', 'type', 'rel').'></link>';
    }
}