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
     */
    static public function tagName(): string
    {
        return 'a';
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
            'target' => Type::STRING,
            'title' => Type::STRING,
            'rel' => Type::STRING,
            'nodeValue' => Type::PROPERTY,
        ];
    }

    /**
     * Get formatted tag.
     */
    public function tag(): string
    {
        return '<a'.$this->tagAttributes('href', 'target', 'title', 'rel').'>'.$this->nodeValue.'</a>';
    }
}