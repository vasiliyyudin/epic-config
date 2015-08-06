## Requirements

* PHP 5.5+

## Installation

Add the plugin to your project's `composer.json`:

```composer
  {
    "require": {
      "epic/config": "dev-master"
    }
  }
```

## Usage

Create a new loader:

```php
<?php
$config = new \Epic\Config(['path/to/.env']);
var_dump($config['param']);

?>
```
