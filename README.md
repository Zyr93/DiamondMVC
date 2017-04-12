# DiamondMVC

### a web developer's scaffolding or the Linux of web server platforms.

The objective of *the Diamond* is to accelerate the development process of any kind of web
project. It has been created mostly within a single month of work.

*The Diamond* is not a CMS. The Diamond is a pure MVC (with one exception, but please
forgive this design choice in favor of simplicity in use. :)) It focuses on the developer,
not on the end user. It is not shipped with a bunch of fancy tools to make website creation
visually easy. Instead its structure is designed to allow a developer to navigate quickly
and easily through their website with advanced flexibility, control and extensibility.
Its file system is generally laid out similar to that of Linux, using a centralized registry
to track where components where installed and to check for and deploy updates.

It is currently a rather bare bones framework designed as merely the launch pad
to your application. Working in *the Diamond* encourages working modularly in all aspects of
your website: PHP, JavaScript and even CSS. Yet encourages working in your own coding style!

## Installation

This repository is a working copy. As such installation takes a single manual extra step:
simply rename the firstinstallation.bak folder to firstinstallation and the system will
recognize the change the next time the website is visited!

Accordingly I advise to set up and configure the website offline, then simply copy all files
without exception to the desired location on your server for deployment.

After installation the folder firstinstallation will be deleted to remove this potential
security hole.

*Note: The Build directory is irrelevant for deployment and merely contains a custom JavaScript
for use under NodeJS to create an appropriate deployment with version information and changes
determined by git to automatically create a bare bones update script. Feel free to use this
script for your own extensions.*

## Getting Started

In *the Diamond*, similar to some Linux distros, the location of your files suggests their
function. Similar to various CMS like *Joomla!*, PHP classes are located by their folder
location and their name, if not already loaded. Thus if you feel the need to locate a class
elsewhere, all you need to do is manually include the appropriate files.

Since no real tutorial or wiki exists for this project, I recommend simply browsing through
some of the project's source code, specifically the classes */classes/class_controller.php*,
*/classes/class_model.php*, */classes/class_view.php*, */classes/class_snippet.php*,
*/classes/class_template.php*, */classes/class_module.php*, */classes/class_permissions.php*,
and */classes/class_event.php*.

### The *controllers* directory

In this directory reside the classes which drive the behavior of your website. Controllers
receive and parse user input and connect the data pulled from models with the display called
the view.

### The *models* directory

In this directory reside the classes which determine how and where to read information from.
Usually information will be read from a MySQL/MariaDB database server, but can also be pulled
from a remote web resource.

### The *views* directory

Unlike the above two directories this directory is a bit more complex. It can provide PHP
classes directly, though usually you'll find mere snippets here that your controller can
address and populate with data. Just look at the source code of the bare installation to
get started.

This directory structure also contains view-specific scripts and stylesheets that can be
easily included using the facilities of the MVC. The appropriate code will be generated such
that <link> and <script> tags are where they are supposed to be: in the <head>. (Note: feel
free to overwrite this behavior with the <script> tags yourself if you want them to be at
the end of <body> as is standard as well nowadays.)

### The *assets* directory

This is where you'll find globally relevant libraries for client side use, e.g. *requireJS*,
*jQuery*, or *Bootstrap*. Your website's own JavaScript framework or common CSS could be located
here as well. The MVC's facilities will automatically search for the appropriate file location
if it cannot find scripts or stylesheets in the view's directory structure.

### The *libs* directory

This is where general purpose classes reside which are none of the above, essentially considered
part of the framework. These classes are designed to work in coop with the classes in the *classes*
directory, see below.

### The *classes* directory

This is where classes of a specific group that are still considered part of the framework reside.
For example, the base *Events* class is defined in */libs/class_events.php* whilst specific events
which the framework uses are defined in */classes/events/*.

Generally the classes found here register a custom autoloader for their classes through a file in
*/classes/libs/*. *The Diamond*'s framework exposes a function on its singleton named *loadLibrary*
which takes a single string as the name of the library to load within this specific directory
(*/classes/libs/*). In other words, this is where you can specify custom loading locations, such as
the aforementioned */classes/events/* directory.

I admit this is a little bit confusing due to non-standard naming and if I ever return to this
project (probably when creating my own website becomes a thing again) I'll probably rearrange this
structure to be more clear.

### The *registry* directory

You should never touch this directory. It contains but a single humanly readable named file. All other
files' names are generated. Regardless, this directory contains details on the installed extensions.
*The Diamond* itself is considered an extension as well. This design allows for automated updates just
like with any other extension you can install. Tempering with these files can lead to unexpected
results.

### The *lang* directory

This is where your internationalization files reside. These files exclusively generate .ini formatted
output, with an emphasis on output. You may also provide a PHP file which generates an INI file and
the system will catch the translations. However, when generating through a PHP file, it is recommended
to use the shipped INI class as in the future this is where the system can hook to create more optimized
ini files and cached output.

### The *plugins* directory

All plugins are loaded at startup of the program. From there on out plugins can do whatever they need.
Admittingly their best use is to listen to events triggered in the program, but if I ever get back to
working on this project they could easily be infused with more power by expanding this microlibrary
with interdependencies.

It is possible to get a specific plugin by name through the main singleton's API which in turn allows
the plugin to expose a custom API.

### Regarding Scripts

An extremely important note regarding scripts is that *the Diamond* uses *requireJS* for
asynchronous module loading and interdependence and I find it to be a great complement to
the server side modularity on client side.

RequireJS has been configured to work with *the Diamond*'s directory structure and scripts you
include in your view (or controller, really) are communicated to RequireJS through the main
script determined in its configuration through a JSON array included in the <head> at the
creation of the web page.

## Conclusion

I hope this brief introduction is enough to get a firm grasp of how to work with *the Diamond* and
I apologize for the lack of official documentation. As you can easily see the last commit to this project
was over a year ago since I am not very engaged in web design anymore.
