# API docs generator
This is a very basic script that renders the YAML file (in the OpenAPI 3.0 spec) that is used as the source for Firefly III's API documentation. I wrote it because the API YAML file was well over 1400 lines before I got sick of editing such a large file. The current stitched result is well over 7500 lines at the time of writing.


## How it works
What it does is simply join all YAML files in `/yaml/` together, preceded by the `start.yaml.twig` file in the `/templates/` directory.

1. Rename `.env.example` to `.env` and adjust the variables accordingly, e.g.

    ```dotenv
    API_VERSION=0.10.0
    API_DESTINATION=./
    API_ROOT=./
    ```

2. Install dependencies via [Composer](https://getcomposer.org/)

    `$ composer install`

3. Install PHP `yaml.so` extension. It should be added to your `php.ini` automatically.

    `$ pecl install yaml`

4. Generate `firefly-iii-${API_VERSION}.yaml` file

    `$ php index.php`

The resulting `firefly-iii-${API_VERSION}.yaml` file can be imported into your favorite API development environment to play around with, e.g. [Postman](https://www.getpostman.com/).

![Firefly III API collection in Postman](postman-firefly-iii-collection.png "Firefly III API collection in Postman")

## Current state
I'm actively writing all API documentation, so the file may change.

## Contributing
Feel free to create a PR on anything. If you want to change endpoints, check out `/yaml/paths`. Or edit objects in `/yaml/schemas`. This is my first API document so feel free to tell me how to improve.

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


