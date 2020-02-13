# Bitdoc

## Introduction

Bitdoc is a fast static content generator, without any bells or whistles, written in PHP that converts Markdown to HTML.

It was written as a hack in 2016 during a benchmarking project testing PHP 7 performance improvements compared to PHP 5.6 and HHVM.

Originally Bitdoc had integrated plug-in support with multiple plug-ins, automatic TOC generation, support for multiple markup languages, and many other features, but when the benchmarking project finished I removed those features as I wanted a very simple Markdown generator with manual TOCs. Since then I have been using Bitdoc on my website [unixsheikh.com](https://www.unixsheikh.com/) and since people keep asking me what tool I use to write my articles and tutorials I have released Bitdoc.

Bitdoc needed to be fast so I wrote it in a mix of structured and object-oriented code. The object-oriented part could just as easily have been done in procedural code. However, the performance tests showed no major improvements even though PHP do use an additional opcode call named `INIT_NS_FCALL_BY_NAME`, which means that when PHP runs over this opcode, it will check if the function `call_user_func()` is found in the namespace, and if the function exists in the namespace, it will run it, otherwise it will check if it exists in the global namespace and execute that. The difference was minor, so in order to keep the code easier to maintain and easier to extend during the project, the object-oriented paradigm was chosen for the library files, while the main runtime is in the structured paradigm.

Bitdoc has an internal index database that tracks added content, deleted content, and changed content in the Markdown files. As such it doesn't re-generate HTML files unless needed.

The templates are written in PHP and does not put a template engine on top of PHP. All the HTML markup is put into a variable with added support for PHP logic. This makes it very easy to use custom options in the Yaml options section (optional) as all that is handled in the template.

Bitdoc uses [Parsedown](https://github.com/erusev/parsedown) and [ParsedownExtra](https://github.com/erusev/parsedown-extra) from Emanuil Rusev as they are the fastest and best parsers for PHP. Other parsers was tested, but they where all much slower and produced errors in the HTML.

With the parsers from Emanuil Rusev Bitdoc supports:

- [Markdown](https://en.wikipedia.org/wiki/Markdown)
- [Markdown Extra](https://en.wikipedia.org/wiki/Markdown_Extra)

Markdown filename extensions `.md` and `.markdown` are both supported.

## Installation

You need to have [PHP CLI](https://www.php.net/manual/en/features.commandline.php) installed.

Clone the Bitdoc repository:

```
$ git clone https://github.com/unixsheikh/bitdoc
```

Use [Composer](https://getcomposer.org/) to install the parser dependencies:

```
$ cd bitdoc
$ composer install
```

## Setup

Copy the file `app/config.example.php` to `app/config.php`.

Edit `app/config.php` if you need to change any of the default settings, such as the name of the Markdown directory or the HTML directory.

By default Bitdoc needs a directory named `markdown` which is where you put the Markdown files. You can create as many sub-directories in the `markdown` directory as you want.

When Bitdoc converts the Markdown documents it will, by default, create a directory named `html` which is where the generated HTML files will reside. The structure found in the `markdown` directory will be cloned to the `html` directory. So if you have a sub-directory called `foo` with some files in it, a directory called `foo` will also be created in the `html` directory with the same files converted to HTML.

## Usage

Bitdoc comes with a couple of simple templates located in the `templates` directory.

#### 1. Simply enter the template directory and copy the template you wish to use to the name `default`.

```
$ cd bitdoc/templates
$ cp -r simplygrey default
```

Then make any changes you see fit to the `default` template.

Don't rename any of the existing templates, but use a copy instead as it will prevent any potential conflicts with updates to a template.

If you decide to try another templates, delete the `default` template and copy another one to `default` instead.

You can also easily create your own template. Just copy one of the templates and start making changes to it. Consider making a pull request and have it added to Bitdoc :)

Whatever include files your template may need has to go into a `includes` directory in the chosen template. Whether JavaScript, CSS, images, or something else. Otherwise Bitdoc won't copy those files from the template directory to the HTML directory.

#### 2. Inside the `bitdoc` directory create a directory called `markdown` and populate it with markdown files as you see fit.

```
$ mkdir markdown
$ vim markdown/index.md
$ mkdir markdown/articles
$ vim markdown/articles/my-first-article.md
```

#### 3. Options

Bitdoc supports basic Yaml options which you can put at the top of each Markdown document using a Markdown comments section.

```
<!---
title: My first article
html-lang: en
post-date: 2019-05-05
modification-date: 2019-08-27
abstract: This is my first article.
--->
```

You can make as many options as you like. All the options gets parsed by Bitdoc and passed on to the logic in the template.

If you want to add an option for a HTML `meta name="author"` tag, you can just add the option in the Markdown file:

```
<!---
title: My first article
html-lang: en
author: Foo Bar
post-date: 2019-05-05
modification-date: 2019-08-27
abstract: This is my first article.
--->
```

And then add the needed PHP logic where you want the result to be put into the HTML variable in the template:

```
...
if (!empty($yaml['author'])) {
    $html .= '<meta name="author" content="'.$yaml['author'].'">';
}
```

The Yaml options in the Markdown comments section will be processed and turned into a PHP array which then will be handed over to the logic in the template. If no options are present, the rest of the document will simply be parsed as Markdown without any Yaml options.

#### 4. TOC

There is no auto-generation of TOCs. TOCs are added manually for fine grained control. A simple Markdown example may looks like this:

```
<!---
title: My first article
html-lang: en
post-date: 2019-05-05
modification-date: 2019-08-27
abstract: This is my first article.
--->

## Table of Contents

- [Foo](#foo)
- [Bar](#bar)
- [Baz](#baz)
    - [Qux](#qux)
    - [Quux](#quux)

## Foo {#foo}

Lorem ipsum dolor sit amet, consectetur adipiscing elit.

## Bar {#bar}

Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.

## Baz {#baz}

Ut enim ad minim veniam.

### Qux {#qux}

Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

### Quux {#quux}

Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
```

#### 5. Generate the HTML.

When you're ready to generate the static HTML, simply type:

```
$ php app/bin/bitdoc.php
```

This is an example of a converted structure:

```
markdown/
├── articles
│   └── foo.md
├── contact.md
├── news
│   └── something
│       └── bar.md
├── hobby
├── hobby.md
│   └── electronics
│       └── baz.md
├── index.md
└── tutorials
    ├── qux.md
    └── quux.md
html/
├── articles
│   └── foo.html
├── contact.html
├── news
│   └── something
│       └── bar.html
├── hobby
├── hobby.md
│   └── electronics
│       └── baz.html
├── index.md
└── tutorials
    ├── qux.html
    └── quux.html
```

**Note:** If you make any changes to the template after you have generated content, you need to manually delete the `html` directory and the `var` directory (it contains the index database) and generate content a new. Currently Bitdoc doesn't track changes to the template.

So, if you have changed the template, or simply want to try out another template, remove the `html` and `var` directories:

```
$ rm -rf html/ var/
```

And generate new content:

```
$ php app/bin/bitdoc.php
```

## Removing directories

If you delete a directory within your Markdown folder, Bitdoc will delete all corresponding HTML files, but it will not delete the corresponding directory in the HTML folder. The reason for this is that the directory might contain manually added files.

## Error handling

Bitdoc will provide you with status messages on the console as it processes files and generate content. It will also provide you with custom error messages if something should go wrong.

If you ever need PHP errors displayed on the console you need to enable PHP errors in the `app/config.php` file.
