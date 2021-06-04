<?php

namespace App\Console\Commands;

use App\Exceptions\FailToLoadXmlException;
use App\Exceptions\FileNotFoundException;
use App\Jobs\ProcessRssPodcastFeed;
use Illuminate\Console\Command;

class PodcastRssFeed extends Command
{
    protected $signature = 'rss-feed {url : URL of RSS Podcast Feed}';

    protected $description = 'Parse a podcast RSS feed and store data about the podcast and its episodes.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $feed = $this->argument('url');

        try
        {
            ProcessRssPodcastFeed::dispatch($feed);
            $this->info("Podcast Feed RSS imported without errors.");
            return 0;
        }
        catch (FileNotFoundException $ex)
        {
            $this->error($ex->getMessage());
        }
        catch (FailToLoadXmlException $ex)
        {
            $this->error($ex->getMessage());
        }
    }
}
