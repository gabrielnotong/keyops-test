Plateforme de dev
---
- PHP >= 7.1.3
- Mysql >= 5.6.37
- Serveur web: ampps 3.8
- Outil de test api: Postman `or` /api/doc

Installation
---
> clone the project:
 - git clone https://github.com/gabrielnotong/keyops-test.git
> install dependencies
- composer install
- bin/console assets:install
> Configure Database
- edit the file '.env' and change the following line
    DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/database
> Create database
- php bin/console doctrine:database:create
- php bin/console doctrine:migrations:diff
- php bin/console doctrine:migrations:migrate

Unit testing
---
> Configure database
- Edit this file `phpunit.xml.dist` (located in the root of the project) and enter your database parameter here:

`<env name="DATABASE_URL" value="mysql://database_user:database_password@127.0.0.1:3306/database" />`

> Some tests have been implemented
- php bin/phpunit

HOW TO USE THE API
---
- /api/doc

Company API with pagination
---
> Parameters to use are
- filter = to filter on company name
- order = to order the result "asc" or "desc"
- limit = number of elements per page
- offset = page number
> usage example
/api/companies?filter=xxx&order=asc&limit=5&offset=1

Sonanta Admin 
---
- /admin
