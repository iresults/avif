<?php

declare(strict_types=1);

namespace Iresults\Avif\Converter;

use RuntimeException;

interface Converter
{
    public function __construct(string $parameters);

    /**
     * Converts a file $originalFilePath to avif in $targetFilePath.
     *
     * @throws RuntimeException
     */
    public function convert(string $originalFilePath, string $targetFilePath): void;
}
