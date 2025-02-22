<?php

declare(strict_types=1);

namespace Iresults\Avif\Service;

use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Iresults\Avif\Converter\Converter;
use Iresults\Avif\Converter\Exception\ConvertedFileLargerThanOriginalException;
use Iresults\Avif\Converter\Exception\WillNotRetryWithConfigurationException;
use Iresults\Avif\Converter\ExternalConverter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function explode;
use function filesize;
use function in_array;
use function is_file;
use function sprintf;
use function strtolower;

final class Avif
{
    /**
     * @param FileInterface|File $originalFile
     *
     * @throws ConvertedFileLargerThanOriginalException
     * @throws WillNotRetryWithConfigurationException
     */
    public function process(FileInterface $originalFile, ProcessedFile $processedFile): void
    {
        if ('avif' === $originalFile->getExtension()) {
            return;
        }

        $processedFile->setName($originalFile->getName() . '.avif');
        $processedFile->setIdentifier($originalFile->getIdentifier() . '.avif');

        $originalFilePath = $originalFile->getForLocalProcessing(false);
        if (!@is_file($originalFilePath)) {
            return;
        }

        $targetFilePath = "{$originalFilePath}.avif";

        $converterClass = ExternalConverter::class;
        $parameters = $this->getParametersForMimeType($originalFile->getMimeType());

        if (!empty($parameters)) {
            if ($this->hasFailedAttempt((int) $originalFile->getUid(), $parameters)) {
                throw new WillNotRetryWithConfigurationException(sprintf('Conversion for file "%s" failed before! Will not retry with this configuration!', $originalFilePath));
            }

            /** @var Converter $converter */
            $converter = GeneralUtility::makeInstance($converterClass, $parameters);
            $converter->convert($originalFilePath, $targetFilePath);
            $fileSizeTargetFile = @filesize($targetFilePath);
            if ($fileSizeTargetFile && $originalFile->getSize() <= $fileSizeTargetFile) {
                $this->saveFailedAttempt((int) $originalFile->getUid(), $parameters);
                throw new ConvertedFileLargerThanOriginalException(sprintf('Converted file (%s) is larger (%d vs. %d) than the original (%s)!', $targetFilePath, $fileSizeTargetFile, $originalFile->getSize(), $originalFilePath));
            }
            $processedFile->updateProperties(
                [
                    'width'  => $originalFile->getProperty('width'),
                    'height' => $originalFile->getProperty('height'),
                    'size'   => $fileSizeTargetFile,
                ]
            );

            return;
        }

        throw new InvalidArgumentException(sprintf('No options given for adapter "%s" and mime type "%s" (file "%s")!', $converterClass, $originalFile->getMimeType(), $originalFile->getIdentifier()));
    }

    public static function isSupportedMimeType(string $mimeType): bool
    {
        $supportedMimeTypes = (string) Configuration::get('mime_types');
        if (!empty($supportedMimeTypes)) {
            return in_array(strtolower($mimeType), explode(',', strtolower($supportedMimeTypes)), true);
        }

        return false;
    }

    private function getParametersForMimeType(string $mimeType): ?string
    {
        $parameters = explode('|', Configuration::get('parameters'));
        foreach ($parameters as $parameter) {
            $typeAndOptions = explode('::', $parameter, 2);
            $type = $typeAndOptions[0] ?? null;
            $options = $typeAndOptions[1] ?? null;
            // Fallback to old options format
            if (empty($options)) {
                return $type;
            }
            if ($type === $mimeType) {
                return $options;
            }
        }

        return null;
    }

    private function saveFailedAttempt(int $fileId, string $configuration): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_avif_failed');
        $queryBuilder->insert('tx_avif_failed')
            ->values([
                'file_id'            => $fileId,
                'configuration'      => $configuration,
                'configuration_hash' => md5($configuration),
            ])
            ->executeStatement();
    }

    private function hasFailedAttempt(int $fileId, string $configuration): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_avif_failed');

        try {
            return (bool) $queryBuilder->count('uid')
                ->from('tx_avif_failed')
                ->where(
                    $queryBuilder->expr()->eq('file_id', $fileId),
                    $queryBuilder->expr()->eq('configuration_hash',
                        $queryBuilder->createNamedParameter(md5($configuration)))
                )
                ->executeQuery()
                ->fetchOne();
        } catch (Exception $e) {
            return false;
        }
    }
}
