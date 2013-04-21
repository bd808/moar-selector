Moar-Selector
=============

Select a target value from an object, object graph or array.

Part of the [Moar PHP Library][].

[![Build Status][ci-status]][ci-home]


Installation
------------
Moar-Selector is available on Packagist ([moar/selector][]) and is installable
via [Composer][].

    {
      "require": {
        "moar/selector": "dev-master"
      }
    }


If you do not use Composer, you can get the source from GitHub and use any
PSR-0 compatible autoloader.

    $ git clone https://github.com/bd808/moar-selector.git


Run the tests
-------------
Tests are automatically performed by [Travis CI][]:
[![Build Status][ci-status]][ci-home]


    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --dev
    phpunit


---
[Moar PHP Library]: https://github.com/bd808/moar
[ci-status]: https://travis-ci.org/bd808/moar-selector.png
[ci-home]: https://travis-ci.org/bd808/moar-selector
[moar/selector]: https://packagist.org/packages/moar/selector
[Composer]: http://getcomposer.org
[Travis CI]: https://travis-ci.org
