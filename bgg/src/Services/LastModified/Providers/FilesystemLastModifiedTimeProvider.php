<?php

/**
 * This file is part of the brandon14/brand0n.gg package.
 *
 * Copyright 2017-2020 Brandon Clothier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

declare(strict_types=1);

namespace Brand0nGG\Services\LastModified\Providers;

use Iterator;
use function is_dir;
use DirectoryIterator;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveCallbackFilterIterator;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider;

// TODO: Add getters and setters.

/**
 * Class FilesystemLastModifiedTimeProvider.
 *
 * Filesystem last modified provider. Will iterate over a list of directories
 * and get the most recently modified file time.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class FilesystemLastModifiedTimeProvider implements LastModifiedTimeProvider
{
    /**
     * Base path to start the file traversal.
     *
     * @var string
     */
    protected string $basePath;

    /**
     * List of directories to traverse to determine last modified file time.
     *
     * @var string[]
     */
    protected array $includedDirectories;

    /**
     * Constructs filesystem last modified provider.
     *
     * @param string   $basePath            Application's base directory
     * @param string[] $includedDirectories Array of included directories to scan
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function __construct(string $basePath, array $includedDirectories = [])
    {
        // Validate the base path.
        if (! is_dir($basePath)) {
            throw new InvalidArgumentException("Base path [{$basePath}] is not a valid directory.");
        }

        // Validate included directories.
        foreach ($includedDirectories as $directory) {
            if (! is_dir($directory)) {
                throw new InvalidArgumentException("Directory [{$directory}] is not a valid directory");
            }
        }

        $this->basePath = $basePath;
        $this->includedDirectories = $includedDirectories;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModifiedTime(): int
    {
        $basePathFiles = new DirectoryIterator($this->basePath);

        // Iterate over each file in the base directory.
        $timestamp = $this->findLastModifiedFileTime($basePathFiles);

        // Make sure we have some subdirectories to iterate through.
        if (count($this->includedDirectories) === 0) {
            return $timestamp;
        }

        // Iterate over each included directory recursively to find the last
        // modified timestamp.
        foreach ($this->includedDirectories as $directory) {
            $iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::FOLLOW_SYMLINKS);
            $filter = new RecursiveCallbackFilterIterator(
                $iterator,
                /**
                 * Callback to filter out hidden files from recursive directory iterator.
                 *
                 * @param \DirectoryIterator $current Current items value
                 *
                 * @return bool true iff file/directory isn't hidden
                 */
                function ($current) {
                    if ($current->getFilename()[0] === '.') {
                        return false;
                    }

                    return true;
                }
            );
            // Should always be a recursive iterator for a directory iterator since that is what the filter is
            // built using.
            /** @psalm-var \DirectoryIterator $dir */
            $dir = new RecursiveIteratorIterator($filter);

            $subDirTimestamp = $this->findLastModifiedFileTime($dir);
            $timestamp = $subDirTimestamp > $timestamp ? $subDirTimestamp : $timestamp;
        }

        return $timestamp;
    }

    /**
     * Function to iterate over an array of files/directories and return
     * the greatest file modified time.
     *
     * @param \DirectoryIterator $files Iterator of files
     *
     * @return int Last modified timestamp
     */
    protected function findLastModifiedFileTime(Iterator $files): int
    {
        $timestamp = -1;

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $mTime = $file->getMTime();
                $timestamp = $mTime > $timestamp ? $mTime : $timestamp;
            }
        }

        return $timestamp;
    }
}
