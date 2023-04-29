 

<p align="center">
    <img src="https://github.com/mosparo/mosparo/blob/master/assets/images/mosparo-logo.svg?raw=true" alt="mosparo logo contains a bird with the name Mo and the mosparo text"/>
</p>

<h1 align="center">
    Symfony Bundle
</h1>
<p align="center">
    This bundle adds the required functionality to use mosparo in your Symfony form.
</p>

![GitHub](https://img.shields.io/github/license/arnaud-ritti/mosparo-bundle)
[![GitHub checks](https://github.com/arnaud-ritti/mosparo-bundle/actions/workflows/release.yaml/badge.svg)](https://github.com/arnaud-ritti/mosparo-bundle/actions/workflows/release.yaml)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/arnaud-ritti/mosparo-bundle)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/0f5b1debea2c4a169e44ee5e09397927)](https://app.codacy.com/gh/arnaud-ritti/mosparo-bundle/dashboard?utm_source=gh\&utm_medium=referral\&utm_content=\&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/0f5b1debea2c4a169e44ee5e09397927)](https://app.codacy.com/gh/arnaud-ritti/mosparo-bundle/dashboard?utm_source=gh\&utm_medium=referral\&utm_content=\&utm_campaign=Badge_coverage)

***

## Description

With this PHP library you can connect to a mosparo installation and verify the submitted data.

## Requirements

To use the plugin, you must meet the following requirements:

* A mosparo project
* Symfony 5.4 or greater
* PHP 8.0 or greater

## Installation

Install this bundle by using composer:

```text
composer require arnaud-ritti/mosparo-bundle
```

## Configuration

### 1. Register the bundle

Register bundle into `config/bundles.php`:

```php
return [
    //...
    Mosparo\MosparoBundle\MosparoBundle::class => ['all' => true],
];
```

### 2. Add configuration files

Setup bundle's config into `config/packages/mosparo.yaml`:

```yaml
mosparo:
  instance_url: '%env(MOSPARO_INSTANCE_URL)%'
  uuid: '%env(MOSPARO_UUID)%'
  public_key: '%env(MOSPARO_PUBLIC_KEY)%'
  private_key: '%env(MOSPARO_PRIVATE_KEY)%'
```

Add your variables to your .env file:

```text
###> mosparo/mosparo-bundle ###
MOSPARO_INSTANCE_URL=https://example.com
MOSPARO_UUID=<your-project-uuid>
MOSPARO_PUBLIC_KEY=<your-project-public-key>
MOSPARO_PRIVATE_KEY=<your-project-private-key>
###< mosparo/mosparo-bundle ###
```

## Usage

### How to integrate re-captcha in Symfony form:

```php
<?php

use Mosparo\MosparoBundle\Form\Type\MosparoType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('captcha', MosparoType::class, [
            'allowBrowserValidation' => false,
            'cssResourceUrl' => '',
            'designMode' => false,
            'inputFieldSelector' => '[name]:not(.mosparo__ignored-field)',
            'loadCssResource' => true,
            'requestSubmitTokenOnInit' => true,
        ]);
    }
}
```

### Additional options

| Parameter                  | Type    | Default value                         | Description                                                                                                                                                                                                                                                                        |
|----------------------------|---------|---------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `allowBrowserValidation`   | Boolean | false                                 | Specifies whether browser validation should be active.                                                                                                                                                                                                                             |
| `cssResourceUrl`           | String  | *empty*                               | Defines the address at which the browser can load the CSS resources. You can use it if the correct resource address is cached.                                                                                                                                                     |
| `designMode`               | Boolean | false                                 | Used to display the mosparo box in the different states in the mosparo backend. The mosparo box is not functional if this option is set to `true`.                                                                                                                                 |
| `inputFieldSelector`       | String  | `[name]:not(.mosparo__ignored-field)` | Defines the selector with which the fields are searched.                                                                                                                                                                                                                           |
| `loadCssResource`          | Boolean | true                                  | Determines whether the script should also load the CSS resources during initialization.                                                                                                                                                                                            |
| `requestSubmitTokenOnInit` | Boolean | `true`                                | Specifies whether a submit token should be automatically requested during initialization. If, for example, the form is reset directly after initialization (with `reset()`), there is no need for a submit token during initialization, as a new code is requested with the reset. |

## Ignored fields

### Automatically ignored fields

mosparo automatically ignores the following fields:

* All fields which **do not** have a name (attribute `name`)
* HTML field type
  * *password*
  * *file*
  * *hidden*
  * *checkbox*
  * *radio*
  * *submit*
  * *reset*
* HTML button type
  * *submit*
  * *button*
* Fields containing `_mosparo_` in the name

### Manually ignored fields

#### CSS class

If you give a form field the CSS class `mosparo__ignored-field`, the field will not be processed by mosparo.

#### JavaScript initialisation

When initializing the JavaScript functionality, you can define the selector with which the fields are searched (see [Parameters of the mosparo field](#additional-options)).

### How to deal with functional and e2e testing:

Mosparo won't allow you to test your app efficiently unless you disable it for the environment you are testing against.

```yaml
# config/packages/mosparo.yaml
mosparo:
    enabled: '%env(bool:MOSPARO_ENABLED)%'
```

```bash
#.env.test or an environment variable
MOSPARO_ENABLED=0
```

## License

mosparo is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
Please see the [LICENSE](LICENSE) file for the full license.

## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING)
