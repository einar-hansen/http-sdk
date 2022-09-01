# Laravel

Add the service config to the existing `config/services.php` file. Its hard to find config files when they have a seperate config file.

For example:
```php
'github' => [
    'uri' => env('GITHUB_URI', 'https://api.github.com'),
    'key' => env('GITHUB_KEY'),
    'timeout' => env('GITHUB_TIMEOUT', 10),
    'retry' => [
        'times' => env('GITHUB_RETRY_TIMES', null),
        'sleep' => env('GITHUB_RETRY_SLEEP', null),
    ]
]
```

Register the Service to the ServiceProvider

```php
/**
 * @return void
 */
public function register(): void
{
    $this->app->singleton(
        abstract: GitHubService::class,
        concrete: fn() => new GitHubService(
            baseUri:    strval(config('services.github.uri')),
            key:        strval(config('services.github.key')),
            timeout:    intval(config('services.github.timeout')),
            retryTimes: intval(config('services.github.retry.times')),
            retrySleep: intval(config('services.github.retry.sleep')),
        ),
    );
}
```

Add the Service in `src/Services` directory, here you make a directory for the Service, like for example `Services\GitHub`
