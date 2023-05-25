# About
This software is intended to be used to register expenses in a ledger so you can you track your personal finances.  
It is written using PHP and is intended to be run on a LAMP (Linux, Apache, MySQL, PHP) stack.  
Also see [History](#history).

# Prerequisites
- Apache or other PHP capable web-server
- PHP 7.0 or higher
- MySQL or MariaDB server

# Installion

1. Create a directory in the webserver root to hold the application files
2. Unzip the contents of phpledger.zip to the directory created above
3. Copy or rename config.sample.json to config.json
4. Edit config.json to suit your needs
5. Create the database using caixa.sql file with mysql -u root -p < caixa.sql
6. Create users in mysql to access the application. The user password should be encrypted with md5 (CREATE USER 'jeffrey'@'localhost' IDENTIFIED BY PASSWORD(md5('*90E462C37378CED12064BB3388827D2BA3A9B689')))

This is still cumbersome and a bit complicated.  
See [Goals](#goals).

# Goals 
Although the software is already in usable state, these are the minimum goals that should be met for each version.

## Version 1.0
- All code is using Object Oriented Programming, and all "legacy" code is removed
- User management is moved out of MySQL and into the application
- Web interface is usable "enough" on mobile devices
- Interface is using pt-PT language
- Installation and upgrade of database is done in-app

## Version 2.0
- Web interface can either use en-US or pt-PT locales
- Localizable strings are moved to resource files
- Databases supported: MySQL, MS SQL, Postgresql

# Design principles
These are the principles that guide the application's development
- Code should be as secure as possible
- Keep external dependencies at the minimum
- Commits on 'main' branch should always be fully working
- Commits on 'develop' branch can be broken, but it should be avoided if possible.
- Some kind of code testing should be in-place, preferably automated before commits on both branches
- Backwards compatibility is kept when possible. Schema changes should be handled by the application.

# License
This software is distributed under GPLv3. See [LICENSE](LICENSE.md) for more details.

# Contact
Antonio Oliveira - [aholiveira@gmail.com](mailto:aholiveira@gmail.com)
Project link - [https://github.com/aholiveira/phpledger](https://github.com/aholiveira/phpledger)

# History
This project started a few years ago, circa 2000, when I wanted to implement an application to manage my own finances.
I knew some programming so I decided to code it in PHP and host it on my own machine.  
Code was "rushed", not very well structured and not following many rules.  
After using the application for some years and learning more about programming, and with my wife's input, more features
and a code rewrite were in dire need, so this project was revived in 2020.  
I know there are probably much better alternatives out there, but this is also a chance for me to grow, experiment and learn.  
If you know PHP, HTML or CSS feel free to contact me if you want to help this project grow. Also, please do contact me if you want to use
it and need some help.  
