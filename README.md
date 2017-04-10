# Laravel Controllers

Controllers for common UI and endpoints in Laravel,
like API authentication, notification message list, notification settings, etc.

## Laravel 5.x

Install the ```saritasa/php-chat-api``` package:

```bash
$ composer require saritasa/php-chat-api
```

Add the ChatServiceProvider service provider in ``config/app.php``:

```php
'providers' => array(
    // ...
    Saritasa\Laravel\Controllers\ChatServiceProvider::class,
)
```

## Exceptions
### ChatException
Should be thrown by class, implementing chat, if there is no
more suitable exception.

**Example**:
```php
function notify($user) {
    if (/* something wrong */) {
        new ChatException("Your message cannot be delibered");
    }
    // ...
}
```

## Contributing

1. Create fork
2. Checkout fork
3. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)**
4. Update README.md to describe new or changed functionality. Add changes description to CHANGE file.
5. When ready, create pull request

## Resources

* [Bug Tracker](http://github.com/saritasa/php-transformers/issues)
* [Code](http://github.com/saritasa/php-transformers)
