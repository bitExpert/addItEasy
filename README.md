# bitexpert/additeasy

addItEasy is a PSR-7 compatible flat-file CMS and static site generator written in PHP.

## Installation

The preferred way of installing `bitexpert/additeasy` is through Composer. Simply add `bitexpert/additeasy` as a dependency:

```
composer.phar require bitexpert/additeasy
```

To initalize a new project simply call the init command of the provided addItEasy cli tool:

```
./vendor/bin/addItEasy init
```

## Using addItEasy as a flat-file CMS

In case you want to run addItEasy locally the following command needs to be executed in the project directory:

```
php -S 0.0.0.0:8080 -t .
```

Open a web browser and point it to http://localhost:8080 so see addItEasy in action.

## Using addItEasy as a static site generator

To export static HTML pages run the export command od the provided addItEasy cli tool:

```
./vendor/bin/addItEasy export
```

The HTML files as well as the assets will get exported to the folder specified in the project configuration 
($EASY_CONF["app"]["exportdir"]).

## License

addItEasy is released under the Apache 2.0 license.
