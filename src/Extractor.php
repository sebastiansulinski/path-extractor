<?php

namespace SSD\PathExtractor;

use tidy;
use Exception;
use DOMElement;
use DOMDocument;
use SSD\PathExtractor\Tags\Tag;

class Extractor
{
    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var string|null
     */
    private $body;

    /**
     * @var array
     */
    private $extensions;

    /**
     * @var array
     */
    private $tidyConfig = [];

    /**
     * @var string
     */
    private $tidyEncoding;

    /**
     * @var string|null
     */
    private $url;

    /**
     * Extract constructor.
     *
     * @param  string|null $body
     */
    public function __construct(string $body = null)
    {
        $this->dom = new DOMDocument;
        $this->body = $body;
    }

    /**
     * Static constructor.
     *
     * @param  string|null $body
     * @return \SSD\PathExtractor\Extractor
     */
    public static function make(string $body = null): self
    {
        return new static($body);
    }

    /**
     * Set body.
     *
     * @param  string $body
     * @return \SSD\PathExtractor\Extractor
     */
    public function for(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the desired extensions.
     *
     * @param $extensions
     * @return \SSD\PathExtractor\Extractor
     */
    public function withExtensions($extensions): self
    {
        $this->extensions = is_array($extensions) ? $extensions : func_get_args();

        return $this;
    }

    /**
     * Add tidy to the flow.
     *
     * @param  array $config
     * @param  string $encoding
     * @return \SSD\PathExtractor\Extractor
     */
    public function withTidy(array $config = [], string $encoding = 'utf8'): self
    {
        $this->tidyConfig = array_merge([
            'clean' => 'yes',
            'output-html' => 'yes',
            'wrap' => 0,
        ], $config);

        $this->tidyEncoding = $encoding;

        return $this;
    }

    /**
     * Set url.
     *
     * @param  string $url
     * @return \SSD\PathExtractor\Extractor
     */
    public function withUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Extract paths.
     *
     * @param  string $class
     * @return array
     * @throws \SSD\PathExtractor\InvalidHtmlException
     */
    public function extract(string $class): array
    {
        try {

            if (!$body = $this->tidy()) {
                return [];
            }

            $this->dom->loadHTML($body);

            $list = $this->dom->getElementsByTagName($class::tagName());

            if (empty($list)) {
                return [];
            }

            $all = [];

            foreach ($list as $element) {
                $all[] = new $class($this->elements($class, $element));
            }

            return $this->filter($all, $class::pathAttribute());

        } catch (Exception $exception) {
            throw new InvalidHtmlException($exception->getMessage());
        }
    }

    /**
     * Purify string.
     *
     * @return string
     */
    private function tidy(): string
    {
        if (empty($this->tidyConfig)) {
            return (string)$this->body;
        }

        return (new tidy)->repairString((string)$this->body, array_merge([
            'clean' => 'yes',
            'output-html' => 'yes',
            'wrap' => 0,
        ], $this->tidyConfig), $this->tidyEncoding);
    }

    /**
     * Extract elements.
     *
     * @param  string $class
     * @param  \DOMElement $element
     * @return array
     */
    private function elements(string $class, DOMElement $element): array
    {
        $attributes = $class::availableAttributes();

        array_walk(
            $attributes,
            function (string &$type, string $field) use ($element, $class) {
                $type = $this->element($class, $type, $field, $element);
            }
        );

        return $attributes;
    }

    /**
     * Get element.
     *
     * @param  string $class
     * @param  string $type
     * @param  string $field
     * @param  \DOMElement $element
     * @return mixed
     */
    private function element(string $class, string $type, string $field, DOMElement $element)
    {
        if ($field === $class::pathAttribute()) {
            return $this->path($element->getAttribute($field));
        }

        if ($type === Tag::TYPE_PROPERTY) {
            return $element->{$field} ?: null;
        }

        if ($type === Tag::TYPE_BOOLEAN) {
            return $element->hasAttribute($field);
        }

        return $element->getAttribute($field) ?: null;
    }

    /**
     * Prepend url if not already present in the path.
     *
     * @param  string $path
     * @return string
     */
    private function path(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (is_null($this->url)) {
            return $path;
        }

        if (!is_null(parse_url($path, PHP_URL_HOST))) {
            return $path;
        }

        return rtrim($this->url, '/').'/'.ltrim($path, '/');
    }

    /**
     * Filter result by extension.
     *
     * @param  array $all
     * @param  string $attribute
     * @return array
     */
    private function filter(array $all, string $attribute): array
    {
        $all = array_values(array_filter($all, function (Tag $tag) use ($attribute) {
            return $tag->{$attribute} !== '';
        }));

        if (empty($this->extensions)) {
            return $all;
        }

        return array_values(array_filter(
            $all,
            function (Tag $item) use ($attribute) {
                return in_array(
                    pathinfo($item->{$attribute}, PATHINFO_EXTENSION),
                    $this->extensions
                );
            }
        ));
    }
}