# Stacheless

> Stacheless is a Statamic addon to run CMS data through Eloquent.

## Compatibility with Statamic

| Stacheless    | Statamic       |
| ------------- | -------------- |
| 0.1.10        | 3.3.48..3.3.49 |
| 0.1.9         | 3.3.38..3.3.47 |

## Features

This addon provides multi-site supported Repository replacements for the file-based Statamic Repositories:

- [x] Entries
- [x] Revisions
- [x] Collections
- [x] Collection Trees
- [x] Navigations
- [x] Navigation Trees
- [X] Globals
- [X] Taxonomies
- [X] Terms
- [X] Assets
- [X] Asset Containers
- [ ] Forms
- [ ] Submissions
- [ ] Blueprints
- [ ] Fieldsets

For Users, Group and Permissions it’s recommended you use the built in Statamic solution: https://statamic.dev/tips/storing-users-in-a-database

## Why?

> A fast and scaleable method to store and access Statamic data via a database.

## How to Install

``` bash
composer require few-far/stacheless
```

## How to Use

Optionally chose some types to keep using the Statamic Repositories:

```
# .env
STACHELESS_GLOBALS=false
```

Then publish and run the migrations:

```
$ php artisan stacheless:migrations
$ php artisan migrate
```

You’re good to go!

For finer control you can publish the package’s config:

```
$ php artisan vendor:publish --tag stacheless-config
```
