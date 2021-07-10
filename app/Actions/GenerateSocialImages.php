<?php

namespace App\Actions;

use function in_array;
use Statamic\Actions\Action;
use Illuminate\Config\Repository;
use App\Jobs\GenerateSocialImagesJob;
use Illuminate\Contracts\Translation\Translator;
use Statamic\Contracts\Globals\GlobalRepository;
use Statamic\Contracts\Entries\Entry as EntryInstance;

/**
 * Class GenerateSocialImages
 *
 * Statamic action to generate social images.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class GenerateSocialImages extends Action
{
    /**
     * Laravel config instance.
     *
     * @var \Illuminate\Config\Repository
     */
    private Repository $config;

    /**
     * Laravel translator instance.
     *
     * @var \Illuminate\Contracts\Translation\Translator
     */
    private Translator $translator;

    /**
     * List of collections.
     *
     * @var array
     */
    public $availableCollections = [];

    /**
     * Constructs a new GenerateSocialImages.
     *
     * @param \Statamic\Contracts\Globals\GlobalRepository $globals    Statamic global set repo
     * @param \Illuminate\Config\Repository                $config     Laravel config repo instance
     * @param \Illuminate\Contracts\Translation\Translator $translator Laravel translator instance
     *
     * @return void
     */
    public function __construct(GlobalRepository $globals, Repository $config, Translator $translator)
    {
        $this->config = $config;
        $this->translator = $translator;

        $handle = $globals->findByHandle('seo');

        if ($handle !== null) {
            $handle = $handle->inDefaultSite();
        }

        if ($handle !== null && $handle->get('use_social_image_generation') && $handle->get('social_images_collections')) {
            $this->availableCollections = $handle->get('social_images_collections');
        }
    }

    /**
     * Determine if the current thing is an entry and if it's opted in to the auto generation config (global).
     *
     * @return boolean
     */
    public function visibleTo($item)
    {
        return $item instanceof EntryInstance && in_array($item->collectionHandle(), $this->availableCollections, true);
    }

    /**
     * Determine if the current user is allowed to run this action.
     *
     * @return boolean
     */
    public function authorize($user, $item)
    {
        return $user->can('edit', $item);
    }

     /**
     * Run the action
     *
     * @return void
     */
    public function run($items, $values)
    {
        GenerateSocialImagesJob::dispatch($items);

        return $this->config->get('queue.default') === 'redis'
            ? $this->translator->choice('strings.social_images_queue', $items)
            : $this->translator->choice('strings.social_images', $items);
    }
}
