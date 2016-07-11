# bitexpert/additeasy

addItEasy is a PSR-7 compatible flat-file CMS and static site generator written in PHP.

## Installation

The preferred way of installing `bitexpert/additeasy` is through Composer. Simply add `bitexpert/additeasy` as a dependency:

```
composer.phar require bitexpert/additeasy
```

To initialize a new project simply call the init command of the provided addItEasy cli tool:

```
./vendor/bin/addItEasy init
```

## Add a template

addItEasy uses [Twig](http://twig.sensiolabs.org) as templating engine. Create a template named "default.twig" in the 
templates folder like this:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ site.title }}</title>
    <base href="http://localhost:8080/" />
</head>
<body>

<!-- Content block -->
{% block content %}
{% endblock %}

</body>
</html>

```

## Adding content

Content files need to placed in the content directory. Content files are basically Twig files which provide the 
[blocks](http://twig.sensiolabs.org/doc/tags/block.html) required by the template.

```
{% extends "default.twig" %}

{% block content %}
<div>
    Hello World!
</div>
{% endblock content %}
```

Every page of your site is represented by a subfolder in the content directory. The folder names build the URL structure 
for your site, nested subfolders are allowed. Each folder needs to be prefixed with a number which is used to indicate 
the sorting order. Each folder needs to contain one twig template file containing the content to display.

The content folder could look like this:
```   
  |-01-home
  |---home.twig
  |-02-about
  |---about.twig
  |-posts
  |---01-post1
  |-----post1.twig
  |---02-post2
  |-----post2.twig
  |---03-post3
  |-----post3.twig
  |---04-post4
  |-----post4.twig  
```

### Twig helper functions

The current page object gets exposed as a variable named "page" in the template, the $EASY_CONF["site"] configuration
array gets exposed as variable named "site". In addition to that addItEasy exposes 3 helper functions to "interact" with
the content pages. 

The children($pagename) function takes a page name as an argument and returns the child pages. 

```
{% for post in children('posts') %}
    {{ post.getName() }}
{% endfor %}
```

The siblings($pagename) function takes a page name as an argument and returns all sibling pages including the current page.

```
{% for mainnav in siblings('home') %}
    {{ mainnav.getName() }}
{% endfor %}
```

The pageblock($page, $blockname) function takes a page object as first argument and the name of the block to render
as a second argument.

```
{% for post in children('posts') %}
    {{ pageblock(post, 'title') }}
{% endfor %}
```

## Running addItEasy

### Running addItEasy as a flat-file CMS

In case you want to run addItEasy locally the following command needs to be executed in the project directory:

```
php -S 0.0.0.0:8080 -t .
```

Open a web browser and point it to http://localhost:8080 so see addItEasy in action.

### Running addItEasy as a static site generator

To export static HTML pages run the export command od the provided addItEasy cli tool:

```
./vendor/bin/addItEasy export
```

The HTML files as well as the assets will get exported to the folder specified in the project configuration.

## License

addItEasy is released under the Apache 2.0 license.
