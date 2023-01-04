<?php

namespace SSD\PathExtractor\Tags;

enum Type: string
{
	case STRING = 'string';
	case BOOLEAN = 'bool';
	case PROPERTY = 'property';
}