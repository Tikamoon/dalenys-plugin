![Tikamoon](https://www.tikamoon.online/template-logo_tikanoir.svg)


## Overview

This plugin enables using Dalenys payments in Sylius based stores.

## Support

TODO

## Demo

TODO

## Installation
```bash
$ composer require tikamoon/dalenys-plugin
```
    
Add plugin dependencies to your AppKernel.php file:
```php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        
        new \Tikamoon\DalenysPlugin\TikamoonDalenysPlugin(),
    ]);
}
```

## Usage

Go to the payment methods in your admin panel. Now you should be able to add new payment method for Dalenys gateway.

## Testing
```bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ yarn install
$ yarn run gulp
$ php bin/console sylius:install --env test
$ php bin/console server:start --env test
$ open http://localhost:8000
$ bin/behat features/*
$ bin/phpspec run
```

## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.
