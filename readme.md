# Ping CRM

A demo application to illustrate how Inertia.js works.

[comment]: <> (![]&#40;https://raw.githubusercontent.com/inertiajs/pingcrm/master/screenshot.png&#41;)

## Installation

Clone the repo locally:

```sh
git clone https://github.com/inertiajs/pingcrm.git pingcrm
cd pingcrm
```

Install PHP dependencies:

```sh
composer install
```

Install NPM dependencies:

```sh
npm ci
```

Build assets:

```sh
npm run dev
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Make sure your redis is configured correctly in order for the queues to work

Run the dev server (the output will give the address):

```sh
php artisan serve
```

Run the Websockets server (the output will give the address):

```sh
php artisan websockets:serve
```

Run multiple QUEUE WORKERs :

```sh
php artisan queue:work
```

You're ready to go! Visit Ping CRM in your browser, and login with:

- **Username:** knikolovv98@gmail.com
- **Password:** koko9899?

## Running tests

To run the Ping CRM tests, run:

```
phpunit
```
