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
     */
    static public function tagName(): string
    {
        return 'link';
    }

    /**
     * Get path attribute.
     */
    static public function pathAttribute(): string
    {
        return 'href';
    }

    /**
     * Get available attributes.
     */
    static public function availableAttributes(): array
    {
        return [
            'href' => Type::STRING,
            'type' => Type::STRING,
            'rel' => Type::STRING,
        ];
    }

    /**
     * Get formatted tag.
     */
    public function tag(): string
    {
        return '<link'.$this->tagAttributes('href', 'type', 'rel').'>';
    }
}