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
## Need help?

If you need support using Firefly III or the associated tools, come find us!

- [GitHub Discussions for questions and support](https://github.com/firefly-iii/firefly-iii/discussions/)
- [Gitter.im for a good chat and a quick answer](https://gitter.im/firefly-iii/firefly-iii)
- [GitHub Issues for bugs and issues](https://github.com/firefly-iii/firefly-iii/issues)
- [Follow me around for news and updates on Twitter](https://twitter.com/Firefly_iii)

<!-- END OF HELP TEXT -->

<!-- SPONSOR TEXT -->
## Donate

If you feel Firefly III made your life better, consider contributing as a sponsor. Please check out my [Patreon](https://www.patreon.com/jc5) and [GitHub Sponsors](https://github.com/sponsors/JC5) page for more information. Thank you for considering.


<!-- END OF SPONSOR -->

