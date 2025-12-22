# PHPLedger

PHPLedger is a personal finance ledger application that allows you to register and track expenses.
It is written in PHP and designed to run on a LAMP (Linux, Apache, MySQL, PHP) stack.
See [History](#history) for the project background.

## Status

**Project status:** Personal project, actively maintained for experimentation and learning.
Not recommended for production use without thorough review and testing.

## Disclaimer

This software is provided "as is" without any warranties. Use at your own risk.
Security and data integrity are the responsibility of the user.

## Prerequisites

- Apache or another PHP-capable web server
- PHP 8.2 or higher
- MySQL or MariaDB server

## Installation

1. Create a directory under the web server root for the application.
2. Extract the zip file into that directory, or clone the git repository.
3. Copy `config/config.sample.json` to `config/config.json`.
4. Edit `config.json` to match your environment.
5. Create the database:
   ```bash
   mysql -u root -p < caixa.sql
   ```

> This process is still somewhat manual. See [Goals](#goals) for ongoing improvements.

## Goals

These are the minimum objectives for each major version:

### Version 1.0

* All code is fully object-oriented; legacy code removed
* User management handled in the application, not the database engine
* Web interface is usable on mobile devices
* Interface uses Portuguese (pt-PT)
* Database installation and upgrades handled in-app

### Version 2.0

* Web interface supports English (en-US) and Portuguese (pt-PT)
* Localizable strings moved to resource files
* Additional database support: MySQL, MS SQL, PostgreSQL

## Development Principles

* Write secure code wherever possible
* Minimise external dependencies
* `main` branch must always be stable and fully working
* `develop` branch may be unstable, but breakages should be minimised
* Automated testing should be implemented and run before commits
* Backward compatibility should be preserved; schema changes handled by the application

## License

Distributed under the GPLv3. See [LICENSE](LICENSE.md) for details.

## Contact

Antonio Oliveira – [aholiveira@gmail.com](mailto:aholiveira@gmail.com)
Project repository: [https://github.com/aholiveira/phpledger](https://github.com/aholiveira/phpledger)

We welcome end-users, developers, and enthusiasts to try PHPLedger, share feedback, and contribute improvements.

## History

This project started around 2000 as a personal finance management tool.
Early versions were coded quickly with minimal structure. After several years of use and learning, the project was revived in 2020 with a complete rewrite, with  valuable input and support from my wife, who helped shape features and usability.

The goal is to provide a usable ledger, experiment with PHP, HTML, and CSS, and improve development skills. Contributions and collaboration are welcome.
