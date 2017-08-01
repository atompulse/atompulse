Resources
---------

  * [Contributing](https://symfony.com/doc/current/contributing/index.html)
  * [Report issues](https://github.com/symfony/symfony/issues) and
    [send Pull Requests](https://github.com/symfony/symfony/pulls)
    in the [main Symfony repository](https://github.com/symfony/symfony)

What is RanBundle?
-----------------
* enables symfony applications to implement a straight forward mechanism in authorizing access based on permissions.
* the Ran bundle is a simple authorization system that can be used for creating simple 2 level hierarchies of authorization:
    - Authorization Group
        - Group Action 1 Authorization
        - Group Action 2 Authorization
        - Group Action N Authorization
* the authorization system is managed by simply adding options to your routes and grouping routes into authorization groups thus creating a 2 level system.

In addition the bundle provides the mechanism to build a simple 2 level customizable menu system fully
integrated with the authorization system, thus eliminating the need to use a separate system for
managing the menu in an application (or to have the menu hardcoded into templates).

The Ran bundle does not provide a security system for authentication BUT can be used with any
custom made or 3rd party bundle(FosUserBundle) since symfony decouples authentication and authorization.
It is recommended to use RanBundle along with FosUserBundle since it already has a nice API to manage users and groups,
also it has all common db wrappers (doctrine, propel, mongo, etc) used in symfony apps.

Requirements
------------
    - PHP 7.1.*
    - symfony 3.*

Installation
------------
1. Summary:
    - install using composer
    - enable the Bundle
    - configure the RanBundle

2. In details:
    - `composer require atompulse/ran-bundle` OR add `"atompulse/ran-bundle": "*"` directly in your composer file
    - add the bundle to your kernel `AppKernel.php` -> `new Atompulse\Bundle\RanBundle\RanBundle()`
    - check the bundle 'config' folder for configuration details & add them to your config.yml (see documentation section for more details)

Documentation
-------------

1. How it works
    - routes are decorated with an option to have a specific authorization name and an authorization group
    - a command is executed `ap:ran:build` to generate the yml files that will be used for building the authorization hierarchy
    - from the generated yml files you create manually the menu system (menu tree) and the authorization module system (what you present to the user)
    - using a simple UI you allow the system admins to manage USER groups and authorization to these USER groups BY using the Authorization Groups
    - in the controllers all actions (routes that you decorated with the options) will be automatically checked for authorization
    - in templates you can easily check for authorization using the `is_granted` function `{% if is_granted('AUTHORIZATION_NAME') %}`

2. Configuration
    - use the yml configs from the bundle's config folder as a template for your config.yml

    *   this is the folder where the bundle will generate the required yml files based on your routing.yml

        generator:
            output: "%kernel.root_dir%/../src/bundles/VendorName/AppBundle/Resources/config/ran"

    *   source folder for the menu configuration

        menu:
            source: "%kernel.root_dir%/../src/bundles/VendorName/AppBundle/Resources/config/ran/menu.yml"
            param: main_menu
            session: application.menu

    *   source folder for the UI tree configuration

        ui_tree:
            source: "%kernel.root_dir%/../src/bundles/VendorName/AppBundle/Resources/config/ran/ui_tree.yml"
            param: ran_ui_tree

    *   security override - special roles which will override the RAN authorization system

        security:
            override: [ROLE_SUPER_ADMIN]


    * menu: check the bundle's config folder for menu.yml as a template for your own menu and to easily understand the content
    * `menu->param` since `menu.yml` is a resource file it needs a parameter entry key that can be referenced
    * `menu->session` this will be an entry in the session that will contain the full menu structure personalized for a user after he has authenticated;
    just after the user has authenticated(onSecurityInteractiveLogin) the content of `menu.yml` is read and items are removed where the current user does not have authorization;
    this data can be accessed and used directly to display the menu in the templates (since the content is already filtered there's no need to check for authorization)
    `{% set menuItems = app.session.get('application.menu') %}`


    * ui_tree: check the bundle's config folder for ran_ui_tree.yml as a template for your own UI tree and to easily understand the content
    * `ui_tree->param` since `ui_tree.yml` is a resource file it needs a parameter entry key that can be referenced
    * in this file we organize the authorization groups into collections (super groups) to easily manage the presentation on the UI

    * security override: this is a mechanism to allow exceptions(overrides) to the authorization IF the developers encounter some special cases

3. Securing actions
    - Check the bundle's config folder for routing.yml to see examples on how to add ran options for routes and thus create authorization names

4. Checking for authorization (permissions)
    - in controllers the authorization for an action that has been integrated is checked automatically so you dont have to check manually for authorization
    - in templates use `is_granted` function (twig) `{% if is_granted('AUTHORIZATION_NAME') %}` to simply check for a specific action authorization OR an entire authorization group

5. UI integration
    - it is recommend to use the FosUserBundle to manage the users/groups
    - the UI tree can be integrated using JS libraries like dynatree (http://wwwendt.de/tech/dynatree/doc/dynatree-doc.html) or iVantage Treeview module (for angularjs) or any other library that can build a tree structure out of json data
    - the system provides a controller trait `RanManagementTrait` that can be used in your controller to process the input/output from the UI and store the authorization data into FosUserBundle tables
