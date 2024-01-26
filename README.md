# Autolooter

This project was initially started to get a tool that helps me and my friends organize loottables for our PnP round.

The idea was to give the website all the tables and then have a button to get a random item.

# Table of Contents

1. [Requirements](#requirements)
2. [Setup Project](#setup-project)
3. [Setup Database](#setup-database)
    1. [Docker Setup](#docker-setup)
    2. [SQL Setup](#sql-setup)
4. [Demo Data](#demo-data)
5. [Routes](#routes)
6. [How I develop](#how-i-develop-this-project)

# Requirements

- [PHP](https://www.php.net/downloads.php) 8.2
- [Composer](https://getcomposer.org/download/)/[Symfony](https://symfony.com/doc/current/setup.html) 7
- [npm](https://nodejs.org/en/download)
- [Docker](https://www.docker.com/products/docker-desktop/) **OR** an SQL-Server you have access to and can sneak a
  database into

# Setup Project

- Clone repo
- ```composer install```
- ``npm install``
- ``npm run dev``
- best case create a .env.local file for your environment such as your **DATABASE_URL**

# Setup Database

## Docker setup

-
add ```DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=10.11.2-MariaDB&charset=utf8mb4"```
to your .env.local (symfony adds the username/password/database).
- turn on the container with ```docker-compose up``` to get the SQL-Container started.

## SQl Setup

-
add ```DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=10.11.2-MariaDB&charset=utf8mb4"```
to your .env.local
- replace db_user/db_password/db_name with your credentials.

# Demo Data

I prepared some fixtures to run and add some demo values for development.
Included are two users:

| User       | Password   | Roles      |
|------------|------------|------------|
| base_user  | base_user  | ROLE_USER  |
| admin_user | admin_user | ROLE_ADMIN |

The base user has all the rarities, tables and items from the fixtures.
The admin user has access to the backend but no rarities, tables and items prepared.

- if you want some demo data run ``symfony console doctrine:fixtures:load``
- I had some issues on my machine where the mysql-docker server shuts down sometimes. Repeating the statement above
  should result in success.

# Routes

| Controller             | Route Name        | Route                    | Parameter                                                                                                                                                                                                                                                                                        | Purpose                                                                                         |
|------------------------|-------------------|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|
| AdminController        | admin_index       | /admin                   | -                                                                                                                                                                                                                                                                                                | landing page for the admin page                                                                 |
|                        | admin_users       | /admin/users             | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | listing page for the users                                                                      |
|                        | admin_user_edit   | /admin/user/{id?}        | App\Entity\User(nullable) <br/> Symfony\Component\HttpFoundation\Request <br/>Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                         | edit page for the users and creation page if **id** is null                                     |
|                        | admin_user_delete | /admin/user/delete/{id?} | App\Entity\User <br/> Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                                                                          | deletes the user                                                                                |
| HomeController         | app_home          | /                        | -                                                                                                                                                                                                                                                                                                | homepage for the whole app                                                                      |
|                        | user_home         | /home                    | -                                                                                                                                                                                                                                                                                                | homepage for the user                                                                           |
| ItemController         | item_index        | /item                    | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | listing page for the items                                                                      |
|                        | item_detail       | /item/edit/{id?}         | App\Entity\Item(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                   | edit page for items                                                                             |
|                        | item_new          | /item/new                | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | route to use and redirect a null value to the edit page to create new entity                    |
|                        | item_random       | /item/random             | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | site that renders a random item from all items                                                  |
|                        | item_delete       | /item/delete/{id}        | App\Entity\Item <br/> Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                                                                          | deletes the item                                                                                |
|                        | api_item_detail   | /api/item/edit/{id?}     | App\Entity\Item(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                   | renders a the item form in order to be used in modals or other places that only need the form   |
| RarityController       | rarity_index      | /rarity                  | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | listing page for the rarities                                                                   |
|                        | rarity_detail     | /rarity/edit/{id?}       | App\Entity\Rarity(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                 | edit page for rarities                                                                          |
|                        | rarity_new        | /rarity/new              | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | route to use and redirect a null value to the edit page to create new entity                    |
|                        | rarity_delete     | /rarity/delete/{id}      | App\Entity\Rarity <br/> Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                                                                        | deletes the rarity                                                                              |
|                        | api_rarity_detail | /api/rarity/edit/{id?}   | App\Entity\Rarity(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                 | renders a the rarity form in order to be used in modals or other places that only need the form |
| RegistrationController | app_register      | /register                | Symfony\Component\HttpFoundation\Request <br/>Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface <br/> Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface <br/>App\Security\LoginAuthenticator <br/>Doctrine\ORM\EntityManagerInterface                  | registration page                                                                               |
|                        | app_verify_mail   | /verify/mail             | Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface <br/>App\Repository\UserRepository                                                                                                                                                               | endpoint to verify the mail of a user                                                           |
| SecurityController     | app_login         | /login                   | Symfony\Component\Security\Http\Authentication\AuthenticationUtils                                                                                                                                                                                                                               | login page                                                                                      |
|                        | app_logout        | /logout                  | -                                                                                                                                                                                                                                                                                                | logout route - will only redirect to the login page                                             |
| TableController        | table_index       | /table                   | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | listing page for the tables                                                                     |
|                        | table_detail      | /table/edit/{id?}        | App\Entity\Table(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                  | edit page for tables                                                                            |
|                        | table_new         | /table/new               | Symfony\Component\HttpFoundation\Request                                                                                                                                                                                                                                                         | route to use and redirect a null value to the edit page to create new entity                    |
|                        | table_delete      | /table/delete{id}        | App\Entity\Table <br/> Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                                                                         | deletes the table                                                                               |
|                        | api_table_detail  | /api/table/edit/{id?}    | App\Entity\Table(nullable) <br/>Symfony\Component\HttpFoundation\Request <br/>Symfony\Contracts\Translation\TranslatorInterface                                                                                                                                                                  | renders a the table form in order to be used in modals or other places that only need the form  |

# How I develop this project

Fun fact, it sure is not the optimal way, but it works for me :)

- develop on a Windows 11 machine
- run docker Desktop with WSL 2 for the db image
- run npm ```npm run watch``` to track all my style changes
- run symfony server ```syfmony server:start -d``` as local server
- hack away and have fun :)