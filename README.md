# API docs generator
This is a very basic script that renders the YAML file (in the OpenAPI 3.0 spec) that is used as the source for Firefly III's API documentation. I wrote it because the API YAML file was well over 1400 lines before I got sick of editing such a large file. The current stitched result is well over 7500 lines at the time of writing.


## How it works
What it does is simply join all YAML files in `/yaml/` together, preceded by the `start.yaml.twig` file in the `/templates/` directory.

## Current state
I'm actively writing all API documentation, so the file may change.

## Contributing
Feel free to create a PR on anything. If you want to change endpoints, check out `/yaml/paths`. Or edit objects in `/yaml/schemas`. This is my first API document so feel free to tell me how to improve.