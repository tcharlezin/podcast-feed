<?php

namespace App\Jobs;

use App\Exceptions\FailToLoadXmlException;
use App\Exceptions\FileNotFoundException;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessRssPodcastFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $podcast;
    private $feedUrl;
    private $content;
    private $feed;

    public function __construct(string $feedUrl)
    {
        $this->feedUrl = $feedUrl;
    }

    /**
     * @throws FailToLoadXmlException
     * @throws FileNotFoundException
     */
    public function handle()
    {
        Log::info("Starting...");
        $this->verifyFeedUrl();
        $this->loadFeed();
        $this->createOrUpdatePodcast();
        $this->createOrUpdateEpisode();
        Log::info("Finished...");
    }

    private function verifyFeedUrl()
    {
        try
        {
            $this->content = file_get_contents($this->feedUrl);
        }
        catch (\Exception $ex)
        {
            $message = sprintf("URL %s not found! Verify or try again later.", $this->feedUrl);
            Log::error($message);
            throw new FileNotFoundException($message);
        }
    }

    private function loadFeed()
    {
        try
        {
            $this->feed = simplexml_load_string($this->content);
        }
        catch (\Exception $ex)
        {
            $message = sprintf("Failed to load XML from %s", $this->feedUrl);
            Log::error($message);
            throw new FailToLoadXmlException($message);
        }
    }

    private function createOrUpdatePodcast()
    {
        Log::info("Creating podcast...");
        $values = [
            "title" => (string) $this->feed->channel->title,
            "artwork_url" => (string) $this->feed->channel->image->url,
            "rss_url" => $this->feedUrl,
            "description" => (string) $this->feed->channel->description,
            "language" => (string) $this->feed->channel->language,
            "website_url" => (string) $this->feed->channel->link,
        ];

        $this->podcast = Podcast::firstOrCreate($values);

        Log::info(sprintf('Podcast %s created!', $this->podcast->title));
    }

    private function createOrUpdateEpisode()
    {
        Log::info("Creating episodes...");
        foreach($this->feed->channel->item as $episode)
        {
            $enclosure = (array) $episode->enclosure->attributes();
            $enclosureAttributes = (object) $enclosure['@attributes'];

            $values = [
                "podcast_id" => $this->podcast->id,
                "title" => (string) $episode->title,
                "description" => (string) $episode->description,
                "audio_url" => (string) $enclosureAttributes->url,
            ];

            $episode = Episode::firstOrCreate($values);
            Log::info(sprintf('--- Episode %s created!', $episode->title));
        }
    }
}
