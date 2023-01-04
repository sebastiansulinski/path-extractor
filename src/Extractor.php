<?php

namespace SSD\PathExtractor;

use tidy;
use Exception;
use DOMElement;
use DOMDocument;
use SSD\PathExtractor\Tags\Tag;
use SSD\PathExtractor\Tags\Type;

class Extractor
{
	private DOMDocument $dom;

	private ?string $body;

	private array $extensions;

	private array $tidyConfig = [];

	private string $tidyEncoding;

	private ?string $url = null;

	/**
	 * Extract constructor.
	 */
	public function __construct(string $body = null)
	{
		$this->dom = new DOMDocument;
		$this->body = $body;
	}

	/**
	 * Static constructor.
	 */
	public static function make(string $body = null): self
	{
		return new static($body);
	}

	/**
	 * Set body.
	 */
	public function for(string $body): self
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Set the desired extensions.
	 */
	public function withExtensions($extensions): self
	{
		$this->extensions = is_array($extensions) ? $extensions : func_get_args();

		return $this;
	}

	/**
	 * Add tidy to the flow.
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
	 */
	public function withUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Extract paths.
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
	 */
	private function tidy(): string
	{
		if (empty($this->tidyConfig)) {
			return (string) $this->body;
		}

		return (new tidy)->repairString((string) $this->body, array_merge([
			'clean' => 'yes',
			'output-html' => 'yes',
			'wrap' => 0,
		], $this->tidyConfig), $this->tidyEncoding);
	}

	/**
	 * Extract elements.
	 */
	private function elements(string $class, DOMElement $element): array
	{
		$attributes = [];

		foreach ($class::availableAttributes() as $field => $type) {
			$attributes[$field] = $this->element(
				$class, $type, $field, $element
			);
		}

		return $attributes;
	}

	/**
	 * Get element.
	 */
	private function element(string $class, Type $type, string $field, DOMElement $element): mixed
	{
		if ($field === $class::pathAttribute()) {
			return $this->path($element->getAttribute($field));
		}

		if ($type === Type::PROPERTY) {
			return $element->{$field} ?: null;
		}

		if ($type === Type::BOOLEAN) {
			return $element->hasAttribute($field);
		}

		return $element->getAttribute($field) ?: null;
	}

	/**
	 * Prepend url if not already present in the path.
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