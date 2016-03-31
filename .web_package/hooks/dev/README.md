Files ending in `.sh` and `.php` added to this folder will be executed in alphabetical order when you call `bump dev`.

The intention is that these files should set up your project for active development, a state that would be reversed by the build scripts.

An example of this is symlinking to dependencies elsewhere on your local server.