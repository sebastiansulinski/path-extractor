<?php

namespace SSD\PathExtractor;

use DOMDocument;
use SSD\PathExtractor\Tags\Tag;
use SSD\PathExtractor\Tags\Image;
use SSD\PathExtractor\Tags\Anchor;
use SSD\PathExtractor\Tags\Script;

class Extractor
{
    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * Extract constructor.
     *
     * @param  string $string
     */
    public function __construct(string $string)
    {
        $this->dom = new DOMDocument;
        @$this->dom->loadHTML($string);
    }

    /**
     * Extract all images.
     *
     * @param  array|null $extensions
     * @return array
     */
    public function img(array $extensions = null): array
    {
        $tags = $this->dom->getElementsByTagName('img');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Image([
                'src' => $tag->getAttribute('src'),
                'alt' => $tag->getAttribute('alt'),
            ]);
        }

        return $this->filter($all, 'src', $extensions);
    }

    /**
     * Extract all scripts.
     *
     * @return array
     */
    public function script(): array
    {
        $tags = $this->dom->getElementsByTagName('script');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Script([
                'src' => $tag->getAttribute('src'),
                'async' => $tag->hasAttribute('async'),
                'defer' => $tag->hasAttribute('defer'),
            ]);
        }

        return $all;
    }

    /**
     * Extract all documents.
     *
     * @param  array $extensions
     * @return array
     */
    public function a(array $extensions = null): array
    {
        $tags = $this->dom->getElementsByTagName('a');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Anchor([
                'href' => $tag->getAttribute('href'),
                'target' => $tag->getAttribute('target'),
                'title' => $tag->getAttribute('title'),
                'nodeValue' => $tag->nodeValue,
            ]);
        }

        return $this->filter($all, 'href', $extensions);
    }

    /**
     * Filter result by extension.
     *
     * @param  array $all
     * @param  string $attribute
     * @param  array|null $extensions
     * @return array
     */
    private function filter(array $all, string $attribute, ?array $extensions): array
    {
        if (is_null($extensions)) {
            return $all;
        }

        return array_values(array_filter(
            $all,
            function (Tag $item) use ($attribute, $extensions) {
                return in_array(pathinfo($item->{$attribute}, PATHINFO_EXTENSION), $extensions);
            }
        ));
    }
}