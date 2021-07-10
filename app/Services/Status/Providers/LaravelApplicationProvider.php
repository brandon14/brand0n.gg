<?php

declare(strict_types=1);

namespace App\Services\Status\Providers;

use Illuminate\Contracts\Config\Repository;
use const PHP_OS;
use const PHP_VERSION;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

class LaravelApplicationProvider implements StatusServiceProvider
{
    /**
     * Laravel version string.
     *
     * @var string
     */
    protected string $laravelVersion;

    /**
     * Application version string.
     *
     * @var string
     */
    protected string $appVersion;

    /**
     * Construct an application status provider.
     *
     * @param \Illuminate\Contracts\Config\Repository $config Laravel config instance
     *
     * @return void
     */
    public function __construct(Repository $config)
    {
        $this->laravelVersion = (string) $config->get('status.application.laravel_version', StatusServiceProvider::STATUS_UNKNOWN);
        $this->appVersion = (string) $config->get('status.application.app_version', StatusServiceProvider::STATUS_UNKNOWN);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        return [
            'website_version'  => $this->appVersion,
            'laravel_version'  => $this->laravelVersion,
            'operating_system' => PHP_OS,
            'php_version'      => PHP_VERSION,
        ];
    }
}
