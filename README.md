# CreamIO Symfony User Bundle

REST API to handle users for a backoffice over [Symfony 4.0][3].

Implemented features listed on the [documentation][4].

##### To Do :

- Test logout
- Create unit tests
- Add more advanced permission handling system
- Make user entity a doctrine MappedSuperClass and make it extendable from the business application.


Requirements
------------

  * Symfony 4;
  * PHP 7.2 or higher;
  * Composer;
  * MySQL database;
  * PDO PHP extension;
  * qraimbault/creamio_symfony_basebundle (included in require);
  * and the [usual Symfony application requirements][1].
  
Installation
------------

Require the bundle from a symfony 4 application.

Add the routes to your application by adding to `config/routes.yaml` the following lines:

```yaml
_creamio_userbundle:
    resource: '@CreamIOUserBundle/Resources/config/routing.xml'
    prefix: /admin/api
```

Handle security by replacing (and adapt to your needs) `config/packages/security.yaml` content with this:
```yaml
# config/packages/security.yaml
security:
    providers:
        db_provider:
            entity:
                class: CreamIO\UserBundle\Entity\BUser
                property: username

    role_hierarchy:
        ROLE_ADMIN:     IS_AWAITING_VALIDATION
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    access_control:
        - { path: /admin/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/api, roles: ROLE_ADMIN }
        - { path: /securedTestRoute, roles: ROLE_ADMIN }

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            anonymous: ~
            pattern: ^/
            json_login:
                check_path: cream_io_user.login
            logout:
                path:   /logout

    encoders:
        CreamIO\UserBundle\Entity\BUser:
            algorithm: bcrypt
            cost: 15
```

Tests
------------

Functionnal tests are made over behat, you can run them from your business application with the following composer requirements :

```json
    "behat/behat": "^3.4",
    "behat/mink": "^1.7.1@dev",
    "behat/mink-browserkit-driver": "@dev",
    "behat/mink-extension": "^2.3",
    "behat/symfony2-extension": "^2.1",
    "behatch/contexts": "^3.1",
    "emuse/behat-html-formatter": "^0.1.0",
    "phpunit/php-code-coverage": "^6.0",
    "rdx/behat-variables": "^1.2",
    "symfony/browser-kit": "~4.0",
    "symfony/dom-crawler": "~4.0",
    "symfony/dotenv": "^4.0",
```

Using the provided `behat.yaml` and if you have XDebug enabled, it will generate a code coverage and functionnal tests report in /docs.


Project tree
-----

```bash
.
├── docs                        # API documentation using Slate as template
│   ├── fonts
│   ├── images
│   ├── javascripts
│   └── stylesheets
├── features                    # Functionnal tests directory
│   ├── bootstrap               # The bootstrap for tests
│   └── references              # JSON schemas for return validation
└── src
    ├── Controller              # API routes controller
    ├── DependencyInjection
    ├── Entity                  # BUser entity
    ├── Repository              # BUser repository
    ├── Resources
    │   └── config              # Service declaration file
    └── Service                 # User management service
```

[1]: https://symfony.com/doc/current/reference/requirements.html
[2]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[3]: https://symfony.com/
[4]: https://qraimbault.github.io/creamio_symfony_userbundle/