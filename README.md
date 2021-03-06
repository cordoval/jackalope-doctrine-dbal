# Jackalope [![Build Status](https://secure.travis-ci.org/jackalope/jackalope-doctrine-dbal.png?branch=master)](http://travis-ci.org/jackalope/jackalope-doctrine-dbal)

A powerful implementation of the [PHPCR API](http://phpcr.github.com).

Jackalope binding for relational databases with the DoctrineDBAL. Works with any
    database supported by doctrine (mysql, postgres, ...) and has no dependency
    on java or jackrabbit. For the moment, it is less feature complete.

Discuss on jackalope-dev@googlegroups.com
or visit #jackalope on irc.freenode.net

License: This code is licenced under the apache license.
Please see the file LICENSE in this folder.


# Preconditions

* phpunit >= 3.6 (if you want to run the tests)
* phpunit/DbUnit (if you want to run the Doctrine DBAL Transport tests)

# Installation

    # in your project directory
    git clone git://github.com/jackalope/jackalope-doctrine-dbal.git
    cd jackalope-doctrine-dbal
    git submodule update --init --recursive

*Be sure to run the git submodule command with recursive to get all dependencies of jackalope.*


## phpunit Tests

If you want to run the tests , please see the [README file in the tests folder](https://github.com/jackalope/jackalope/blob/master/tests/README.md)**


## Enable the commands

There are a couple of useful commands to interact with the repository.

To use the console, copy cli-config.php.dist to cli-config.php and configure the connection parameters.
Then you can run the commands from the jackalope directory with ``./bin/phpcr``

NOTE: If you are using PHPCR inside of Symfony, the DoctrinePHPCRBundle
provides the commands inside the normal Symfony console and you don't need to
prepare anything special.

Jackalope specific commands:

* ``jackalope:init:dbal``: Initialize a database for jackalope with the
    Doctrine DBAL transport.

Commands available from the phpcr-utils:

* ``phpcr:workspace:create <name>``: Create the workspace name in the
    configured repository
* ``phpcr:register-node-types --allow-update [cnd-file]``: Register namespaces
    and node types from a "Compact Node Type Definition" .cnd file
* ``phpcr:dump [--sys_nodes[="..."]] [--props[="..."]] [path]``: Show the node
    names under the specified path. If you set sys_nodes=yes you will also see
    system nodes. If you set props=yes you will additionally see all properties
    of the dumped nodes.
* ``phpcr:purge``: Remove all content from the configured repository in the
     configured workspace
* ``phpcr:sql2``: Run a query in the JCR SQL2 language against the repository
    and dump the resulting rows to the console.



# Bootstrapping

Jackalope relies on autoloading and the namespaces and folder structure follow
PSR-0. Either set up your autoloading to find classes in the following folders
or copy the ``autoload.dist.php`` file in ``src/`` to ``autoload.php`` and
adjust as needed.

If you checked out everything as submodules, the paths will be

* src/
* lib/jackalope/src
* lib/jackalope/lib/phpcr/src
* lib/jackalope/lib/phpcr-utils/src
* lib/jackalope/lib/phpcr-utils/lib/vendor
* lib/vendor/doctrine-dbal/lib
* lib/vendor/doctrine-dbal/lib/vendor/doctrine-common/lib

## Bootstrapping Doctrine DBAL

Before you can use jackalope with a database, you need to set the database up.
Make sure the commands are set up (see above "Enable the commands") and run

    bin/jackalope jackalope:init:dbal

To initialize jackalope in your application, you do

    $driver   = 'pdo_mysql';
    $host     = 'localhost';
    $user     = 'root';
    $password = '';
    $database = 'jackalope';
    $workspace  = 'default'; // to use a non-default workspace, you need to create it first. Just use the command phpcr:workspace:create

    // Bootstrap Doctrine
    $dbConn = \Doctrine\DBAL\DriverManager::getConnection(array(
        'driver'    => $driver,
        'host'      => $host,
        'user'      => $user,
        'password'  => $pass,
        'dbname'    => $database,
    ));

    $repository = \Jackalope\RepositoryFactoryDoctrineDBAL::getRepository(array('jackalope.doctrine_dbal_connection' => $dbConn));
    // dummy credentials, unused
    $credentials = new \PHPCR\SimpleCredentials(null, null);
    $session = $repository->login($credentials, $workspace);


# Usage

The entry point is to create the repository factory. The factory specifies the
storage backend as well. From this point on, there are no differences in the
usage (except for supported features, that is).

    // see Bootstrapping for how to get the session.

    $rootNode = $session->getNode("/");
    $whitewashing = $rootNode->addNode("www-whitewashing-de");
    $session->save();

    $posts = $whitewashing->addNode("posts");
    $session->save();

    $post = $posts->addNode("welcome-to-blog");
    $post->addMixin("mix:title");
    $post->setProperty("jcr:title", "Welcome to my Blog!");
    $post->setProperty("jcr:description", "This is the first post on my blog! Do you like it?");

    $session->save();


See [PHPCR Tutorial](https://github.com/phpcr/phpcr/blob/master/doc/Tutorial.md)
for a more detailed tutorial on how to use the PHPCR API.


# TODO

The best overview of what needs to be done are the functional test failures and
skipped tests. Have a look at tests/inc/DoctrineDBALImplementationLoader.php
to see what is currently not working and start hacking :-)

## Some notes

* Implement moving nodes, DoctrineTransport::modeNode() (and make sure not to violate any constraints during the process)
* Implement usage of NodeTypeDefintions to read/write validation and formatting data correctly (such as auto-creating values, forcing multi-values)
* Implement storage of custom/user-defined NodeTypeDefinitions and such into the database.
* Versioning support
* Refactor storage to implement one one table per database type?
* Optimize database storage more, using real ids and normalizing the uuids and paths?
* Implement parser for JCR-SQL2 and implement it in DoctrineTransport::querySQL().
* Implement parser for Jackrabbit CND syntax for node-type definitions.


# Contributors

* Christian Stocker <chregu@liip.ch>
* David Buchmann <david@liip.ch>
* Tobias Ebnöther <ebi@liip.ch>
* Roland Schilter <roland.schilter@liip.ch>
* Uwe Jäger <uwej711@googlemail.com>
* Lukas Kahwe Smith <smith@pooteeweet.org>
* Benjamin Eberlei <kontakt@beberlei.de>
* Daniel Barsotti <daniel.barsotti@liip.ch>
