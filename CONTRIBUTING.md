# Contributing Guide

Looking to report an issue/bug or make a feature request? Please refer to the [README file](README.md).

---

Thanks for your interest in contributing to this Website!

## Code contributions

Pull requests are welcome!

If you're interested in taking on [an open issue](https://github.com/Pecs1/PCOZ-MS/issues), please comment on it so others are aware.
You do not need to ask for permission nor an assignment.

## Prerequisites & tools

- [XAMPP](https://www.apachefriends.org/) or other local server development environment

Before you start, please note that the ability and knowledge to use these programming languages is **required** and that existing contributors will not actively teach them to you.

- languages
  - [HTML](https://www.w3schools.com/html/)
  - [CSS](https://www.w3schools.com/css/)
  - [PHP](https://www.php.net/manual/en/)
  - [MySQL](https://www.mysql.com/)
  - [JavaScript](https://www.w3schools.com/js/)

- configs
  - configure httpd.conf for XAMPP

    - for linux system
      ```
      <Directory />
        AllowOverride None
        Require all granted
        Options FollowSymLinks
      </Directory>

      DocumentRoot "/opt/lampp/htdocs/public"
      <Directory "/opt/lampp/htdocs/public">
        Options FollowSymLinks
        AllowOverride None
        Require all granted
      </Directory>
      ```

    - for windows system

      ```
      <Directory />
        AllowOverride None
        Require all granted
        Options FollowSymLinks
      </Directory>

      DocumentRoot "C:/xampp/htdocs/public" # or other directory/partition label where xampp is located
      <Directory "C:/xampp/htdocs/public">
        Options FollowSymLinks
        AllowOverride None
        Require all granted
      </Directory>
      ```

## Getting help

If you have questions, please ask in [discussions](https://github.com/Pecs1/PCOZ-MS/discussions) on GitHub. We will do our best to help you.

## Forks

Forks are allowed so long as they abide by [the project's LICENSE](https://github.com/Pecs1/PCOZ-MS/blob/main/LICENSE).

When creating a fork, remember to:

- To avoid confusion with the main website:
  - Change the website name
  - Change the website icon

- Make your own database
  - Change the database connection settings in [config.php](config.php) accordingly.
- Dont forget to configure [httpd.conf](CONTRIBUTING.md#prerequisites--tools)
