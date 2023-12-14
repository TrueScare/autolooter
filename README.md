# Autolooter

This project was initially started to get a tool that helps me and my friends organize loottables for our PnP round.

The idea was to give the website all the tables and then have a button to get a random item.

## Requirements
- PHP 8.2
- Composer/Symfony
- npm
- Docker

## Setup
- Clone repo
- docker-compose up to get the SQL-Container started
- ``npm run install``
- ``npm run dev``
- to get the local dev server ``symfony server:start``
- if you want some demo data run ``symfony console doctrine:fixtures:load``
