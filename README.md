# API docs generator

## Introduction

This repository contains the API specification for Firefly III, both "v1" and "v2".

The API specification is generated by stitching together a number of YAML files using a build script, `build-and-deploy.php`.

The results are uploaded to the [API documentation](https://github.com/firefly-iii/api-docs) repository and can be viewed live at [api-docs.firefly-iii.org](https://api-docs.firefly-iii.org/).

## Contributing

Feel free to create a PR on anything. If you want to change endpoints, check out `/yaml/v*/paths`. Or edit objects in `/yaml/v*/schemas`. This is my first API document so feel free to tell me how to improve. Depending on which API you're interested in, browse to `/yaml/v1` or `/yaml/v2` and find the path or schema you want to edit. Make your changes and submit a pull request.

## Templates

You can see lines in the YAML files that look like this:

```yaml
!notFoundResponse,3
```

These are placeholders which will be changed during build. You see the name of the template (see `/yaml/templates/`), and the number tabs to indent the template.

## How to build

Rename `.env.example` to `.env` and adjust the variables accordingly, e.g.

 ```dotenv
 API_DESTINATION=./
 API_ROOT=./
 IS_DEVELOP_RUN=true
 ```

Install PHP `yaml` extension. It should be added to your `php.ini` automatically.

Install dependencies via [Composer](https://getcomposer.org/)

`composer install`

Generate `firefly-iii-v*-develop.yaml` files:

`php build-and-deploy.php`

The resulting `firefly-iii-${API_VERSION}.yaml` file can be imported into your favorite API development environment to play around with, e.g. [Postman](https://www.getpostman.com/).

![Firefly III API collection in Postman](postman-firefly-iii-collection.png "Firefly III API collection in Postman")

## Current state
I'm actively writing all API documentation, so the file may change.

## Why this repository?

The yaml files were getting insanely large and very repetitive, so I cobbled something together. I'm aware there are many API generators out there, that can generate from source, from comments, from whatever. Most however, do not seem to support the complicated objects Firefly III tends to use. I am working on both a simpler API and better documentation, but these tools will probably not fit my use case.

## See the result
You can see the result [on this page](https://api-docs.firefly-iii.org/).

<!-- HELP TEXT -->

## Do you need help, or do you want to get in touch?

Do you want to contact me? You can email me at [james@firefly-iii.org](mailto:james@firefly-iii.org) or get in touch through one of the following support channels:

- [GitHub Discussions](https://github.com/firefly-iii/firefly-iii/discussions/) for questions and support
- [Gitter.im](https://gitter.im/firefly-iii/firefly-iii) for a good chat and a quick answer
- [GitHub Issues](https://github.com/firefly-iii/firefly-iii/issues) for bugs and issues
- <a rel="me" href="https://fosstodon.org/@ff3">Mastodon</a> for news and updates

<!-- END OF HELP TEXT -->

<!-- SPONSOR TEXT -->

## Support the development of Firefly III

If you like Firefly III and if it helps you save lots of money, why not send me a dime for every dollar saved! 🥳

OK that was a joke. If you feel Firefly III made your life better, please consider contributing as a sponsor. Please check out my [Patreon](https://www.patreon.com/jc5) and [GitHub Sponsors](https://github.com/sponsors/JC5) page for more information. You can also [buy me a ☕️ coffee at ko-fi.com](https://ko-fi.com/Q5Q5R4SH1). Thank you for your consideration.

<!-- END OF SPONSOR TEXT -->


