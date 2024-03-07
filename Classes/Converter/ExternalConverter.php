<?php

declare(strict_types=1);

namespace Iresults\Avif\Converter;

use InvalidArgumentException;
use Iresults\Avif\Service\Configuration;
use RuntimeException;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function escapeshellcmd;
use function explode;
use function filter_var;
use function is_executable;
use function is_file;
use function sprintf;

/**
 * Uses an external binary (e.g. avifenc).
 */
final class ExternalConverter extends AbstractConverter
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $parameters)
    {
        $binary = $this->getBinary($parameters);
        if (!is_executable($binary)) {
            throw new InvalidArgumentException(sprintf('Binary "%s" is not executable!', $binary));
        }

        parent::__construct($parameters);
    }

    public function convert(string $originalFilePath, string $targetFilePath): void
    {
        $silent = filter_var(Configuration::get('silent'), FILTER_VALIDATE_BOOLEAN);
        $silent = false;

        [$_, $arguments] = explode(' ', $this->parameters, 2);
        $arguments = str_replace('{input}', $originalFilePath, $arguments);
        $arguments = str_replace('{output}', $targetFilePath, $arguments);

        $binary = $this->getBinary($this->parameters);
        $command = escapeshellcmd($binary . ' ' . $arguments) . ($silent ? ' >/dev/null 2>&1' : '');
        CommandUtility::exec($command);
        GeneralUtility::fixPermissions($targetFilePath);

        if (!@is_file($targetFilePath)) {
            throw new RuntimeException(sprintf('File "%s" was not created!', $targetFilePath));
        }
    }

    private function getBinary(string $parameters): string
    {
        $binary = explode(' ', $parameters, 2)[0];
        $home = $_SERVER['HOME'] ?? '';
        if (str_starts_with($binary, '$HOME/')) {
            return $home . '/' . substr($binary, 6);
        }
        if (str_starts_with($binary, '~/')) {
            return $home . '/' . substr($binary, 2);
        }

        return $binary;
    }
}
