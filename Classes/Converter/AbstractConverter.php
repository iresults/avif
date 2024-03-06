<?php

declare(strict_types=1);

namespace Iresults\Avif\Converter;

abstract class AbstractConverter implements Converter
{
    public function __construct(protected readonly string $parameters)
    {
    }

    abstract public function convert(string $originalFilePath, string $targetFilePath): void;
}
