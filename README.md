## About

Parse a podcast RSS feed and store data about the podcast and its episodes

## Setup

Info: You will need docker installed to continue.

- `cp .env.example .env`
- `docker run --rm -v $(pwd):/app laravelsail/php80-composer:latest bash -c "cd app/ && composer install"`
- `./vendor/bin/sail up -d`
- `./vendor/bin/sail artisan migrate`
- `./vendor/bin/sail artisan rss-feed RSS_PODCAST_FEED_URL`
