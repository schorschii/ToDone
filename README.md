# ToDone
Self-hosted / on-prem todo management system. Intentionally kept as simple as possible.

## System Requirements
This app runs on a classical LAMP stack (Linux, Apache Webserver, MySQL/MariaDB, PHP 7+).

The PHP PEAR mail library is required (package `php-mail` and `php-mail-mime` on Debian/Ubuntu).

## Features
- create tasks and assign them to persons
- assiged person gets an email with a link with which the person can mark it as done
- not assigned tasks can be taken freely by page visitors

## Installation
1. Create a database on your MySQL/MariaDB server and import the database schema from `sql/SCHEMA.sql`.
2. Copy all files onto your webserver.
3. Create the config file `config.php` by copying the example `config.php.example`.
   - Enter you database credentials.
   - Set an admin username and password.
4. Make sure that your mail system is correctly set up on your server so that the app can send invitation mails.
   - On managed servers, this is probably already done by your hosting provider.
5. (optional) If you are using this for a private event, you probably want to lock the access to this webapp with a [.htaccess/.htpasswd file](https://wiki.selfhtml.org/wiki/Webserver/htaccess/Zugriffskontrolle).
6. Create a cron job for executing `reminder.php` every minute to send reminder mails.

## Usage
1. Open `task.php` in your webbrowser and log in with the username/password you chose in the installation step. Create your tasks.

## Custom Design
You can set a custom background image by placing a file `bg-custom.{jpg|png}` inside the `/img` dir. A custom logo image will be displayed if a file `logo-custom.{jpg|png}` is found. Custom CSS can be injected into the page by placing a file called `custom.css` inside the `/css` dir.

## Support & Cloud-Hosting
You do not have an own web server, you need support with installation or operation or want a special development for your needs? Please [contact me](https://georg-sieber.de/?page=impressum).
