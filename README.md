# Penny Post

Penny Post aims to fix online communication by removing its most
pernicious aspect — instantaneity.

Modern messaging optimizes for speed: read receipts, typing indicators,
notifications the second something lands. That speed comes at a cost —
we write faster than we think, and we feel pressure to reply before
we've had a chance to. Letters never worked that way. You wrote when
you had something to say, sent it, and got on with your life until it
arrived.

Penny Post brings that rhythm online. Write to someone whenever the
moment strikes — draft it, come back to it, take your time. Once
you seal a letter, it waits with everyone else's until the next
delivery: every Friday at noon, all at once. No inbox to refresh in
between, no indication anything is coming until it's there.


## Run tests

```sh
composer test
```


## Run locally

```sh
php artisan serve
```

## To deploy

Penny Post is deployed to a VPS managed by hetzner. Messages are stored in a SQLite database. 


Make sure to commit build files as I cannot run `npm run build` on the server

```sh
ssh hetzner
cd /var/www/laravel.vjbe.net
git pull
php artisan migrate --force
composer dump-autoload --optimize
```
