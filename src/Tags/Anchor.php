<?php

namespace SSD\PathExtractor\Tags;

/**
 * Class Anchor
 *
 * @package SSD\PathExtractor\Tags
 *
 * @property string $href
 * @property string $target
 * @property string $title
 * @property string $rel
 * @property string $nodeValue
 */
class Anchor extends Tag
{
    /**
     * Get tag name.
     *
     * @return string
     */
    static public function tagName(): string
    {
        return 'a';
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
            'target' => static::TYPE_STRING,
            'title' => static::TYPE_STRING,
            'rel' => static::TYPE_STRING,
            'nodeValue' => static::TYPE_PROPERTY,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<a'.$this->tagAttributes('href', 'target', 'title', 'rel').'>'.$this->nodeValue.'</a>';
    }
}