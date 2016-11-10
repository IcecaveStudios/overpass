# Overpass

[![Build Status]](https://travis-ci.org/IcecaveStudios/overpass)
[![Test Coverage]](https://coveralls.io/r/IcecaveStudios/overpass?branch=develop)
[![SemVer]](http://semver.org)

**Overpass** is a basic pub/sub and RPC system for PHP.

* Install via [Composer](http://getcomposer.org) package [icecave/overpass](https://packagist.org/packages/icecave/overpass)
* Read the [API documentation](http://icecavestudios.github.io/overpass/artifacts/documentation/api/)

## Message Brokers

* [Rabbit MQ / AMQP](src/Amqp)
* Redis (not yet implemented)

## Examples

* Pub/Sub
  * [Publisher](examples/pubsub-publisher)
  * [Subscriber](examples/pubsub-subscriber)
* RPC
  * [Server](examples/rpc-server)
  * [Client](examples/rpc-client)

## Contact us

* Follow [@IcecaveStudios](https://twitter.com/IcecaveStudios) on Twitter
* Visit the [Icecave Studios website](http://icecave.com.au)
* Join `#icecave` on [irc.freenode.net](http://webchat.freenode.net?channels=icecave)

<!-- references -->
[Build Status]: http://img.shields.io/travis/IcecaveStudios/overpass/develop.svg?style=flat-square
[Test Coverage]: http://img.shields.io/coveralls/IcecaveStudios/overpass/develop.svg?style=flat-square
[SemVer]: http://img.shields.io/:semver-1.0.1-green.svg?style=flat-square
