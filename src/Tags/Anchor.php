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
 * @property string $nodeValue
 */
class Anchor extends Tag
{
    /**
     * Get available attributes.
     *
     * @return array
     */
    protected function availableAttributes(): array
    {
        return [
            'href' => static::TYPE_STRING,
            'target' => static::TYPE_STRING,
            'title' => static::TYPE_STRING,
            'nodeValue' => static::TYPE_STRING,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<a'.$this->tagAttributes('href', 'target', 'title').'>'.$this->nodeValue.'</a>';
    }
}