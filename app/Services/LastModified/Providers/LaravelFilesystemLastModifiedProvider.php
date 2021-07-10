<?php

namespace App\Services\LastModified\Providers;

use Brand0nGG\Services\LastModified\Providers\FilesystemLastModifiedTimeProvider;
use Illuminate\Contracts\Config\Repository;

/**
 * Class LaravelFilesystemLastModifiedProvider
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LaravelFilesystemLastModifiedProvider extends FilesystemLastModifiedTimeProvider
{
    public function __construct(Repository $config)
    {
        $basePath = (string) $config->get('lastmodified.filesystem.base_path');
        $includedDirectories = (array) $config->get('lastmodified.filesystem.included_directories');

        parent::__construct($basePath, $includedDirectories);
    }
}
