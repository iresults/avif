<?php

declare(strict_types=1);

namespace Iresults\Avif\Core\Filter;

use Iresults\Avif\Service\Configuration;
use Throwable;
use TYPO3\CMS\Core\Resource\Driver\DriverInterface;

use function preg_match;

final class FileNameFilter
{
    /**
     * Remove generated avif files from file lists,
     * i.e. files ending in .suffix.avif, but not exclusively in .avif.
     */
    public static function filterAvifFiles(
        string $itemName,
        string $itemIdentifier,
        string $parentIdentifier = '',
        array $additionalInformation = [],
        ?DriverInterface $driverInstance = null
    ): int {
        $pattern = self::getPattern();
        if (null !== $pattern && 1 === preg_match($pattern, $itemIdentifier)) {
            return -1;
        }

        return 1;
    }

    public static function getPattern(): ?string
    {
        $pattern = (string) Configuration::get('filter_pattern');
        // Test validity
        try {
            if (empty($pattern) || false === preg_match($pattern, '')) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        return $pattern;
    }
}
