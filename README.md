# Scrutiny

Scrutiny helps your laravel project ensure that its current server
environment is configured and running as planned.

## Installation

To install through composer, add the following to your `composer.json` file:

```json
{
    "require": {
        "brightmachine/scrutiny": "~1.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

The installation intructions can be simplified using the following:

    composer require "brightmachine/scrutiny=~1.0"

## How it works

1. In `AppServiceProvider::boot()`, configure the probes to check for all the things your environment needs in order to run 
2. Set up an `uptime check` in Pingdom to alert you if any of the probes fail to pass 

----

## What probes are available

All probes fall under the namespace `Scrutiny\Probes`:

- `AvailableDiskSpace`
- `Callback`
- `ConnectsToDatabase`
- `ConnectsToHttp`
- `ExecutableIsInstalled`
- `PhpExtensionLoaded`
- `QueueIsRunning`
- `ScheduleIsRunning`

Each check has its own parameters and can be used multiple times where it makes sense.

Some system checks may not be supported on Windows.

----

## How to configure the different probes

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        // …
        $this->configureScrutinyProbes();
    }
    
    public function register()
    {
    }
    
    protected function configureScrutinyProbes()
    {
        \Scrutiny\ProbeManager::configure()
            ->connectsToDatabase()
            ->executableIsInstalled('composer.phar')
            ->custom(
                new MyCustomProbe()
            );
    }
}

```

----

## Custom probes

Use the callback probe to add your own custom checks.

If a check should be skipped, throw a `\Scrutiny\ProbeSkippedException` and to fail
a check, throw any other kind of `Exception`:

```php
<?php
\Scrutiny\ProbeManager::configure()
    ->callback('my custom check', function () {
        if (should_i_skip_the_check()) {
            throw new \Scrutiny\ProbeSkippedException('check skipped');
        }
        
        if (should_i_fail_the_check()) {
            throw new \Exception('check failed');
        }
    });
``` 

----

## How to configure pingdom

Configure a new check in pingdom with the following setting:

1. Add an `uptime check` in pingdom to hit `https://yourdomain.com/~scrutiny/check-probes` where yourdomain.com is your production domain
2. Scrutiny will return an HTTP status of `590 Some Tests Failed` when something is awry – this is a custom code 


----

## Contributing

Any contribution is received with humility and gratitude.

We're not striding for perfection, but gradual improvements. 
This is the spirit in which contributions will be considered.

**Process**:

1. Fork, change, create pull-request
2. Tell us why/how your PR will benefit the project 
3. We may ask you for clarification, but we'll quickly let you know whether or not it's likely your change will be merged

😘 Xx