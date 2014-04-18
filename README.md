predaddy
========
[![Latest Stable Version](https://poser.pugx.org/predaddy/predaddy/v/stable.png)](https://packagist.org/packages/predaddy/predaddy)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/szjani/predaddy/badges/quality-score.png?s=496589a983254d22b4334552572b833061b9bd03)](https://scrutinizer-ci.com/g/szjani/predaddy/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9/mini.png)](https://insight.sensiolabs.com/projects/ad36fc7a-f48d-4919-b20d-90eae34aecd9)
[![Gitter chat](https://badges.gitter.im/szjani/predaddy.png)](https://gitter.im/szjani/predaddy)

|master|1.2|2.1|2.2|
|------|---|---|---|
|[![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=master)](https://travis-ci.org/szjani/predaddy)|[![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=1.2)](https://travis-ci.org/szjani/predaddy)| [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=2.1)](https://travis-ci.org/szjani/predaddy)| [![Build Status](https://travis-ci.org/szjani/predaddy.png?branch=2.2)](https://travis-ci.org/szjani/predaddy)|
|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=master)](https://coveralls.io/r/szjani/predaddy?branch=master)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=1.2)](https://coveralls.io/r/szjani/predaddy?branch=1.2)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=2.0)](https://coveralls.io/r/szjani/predaddy?branch=2.1)|[![Coverage Status](https://coveralls.io/repos/szjani/predaddy/badge.png?branch=2.1)](https://coveralls.io/r/szjani/predaddy?branch=2.2)|

It is a library which gives you some usable classes to be able to use common DDD patterns. Some predaddy components can be used in any projects regardless of the fact that you are using DDD or not.
I have got several ideas from [Google's Guava EventBus](http://code.google.com/p/guava-libraries/wiki/EventBusExplained) and [Axon framework](http://www.axonframework.org/).

Some libraries are used which are just API libraries and you must care for their implementations:

1. [lf4php](https://github.com/szjani/lf4php) for logging. Without an implementation predaddy is not logging.
2. [trf4php](https://github.com/szjani/trf4php) for transaction management. In most cases you will need to use a trf4php implementation (eg. [trf4php-doctrine](https://github.com/szjani/trf4php-doctrine))

Components
----------

For more details see the components:

1. #### [Message handling](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/messagehandling#messagebus)

   It's an annotation based publish/subscribe implementation, can be used any projects even without DDD/CQRS/Event Sourcing.

2. #### [CQRS and Event Sourcing](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/domain#cqrs--event-sourcing)

   Complex solution for handling aggregates, based on the message handling component.

3. #### [Presentation - finders, etc.](https://github.com/szjani/predaddy/tree/2.2/src/predaddy/presentation#paginator-components)

   Common classes and interfaces for handling the read side. It also can be used in any applications.

Examples
--------

You can find some examples in the [sample directory](https://github.com/szjani/predaddy/tree/2.2/tests/src/sample).

A sample project is also available which shows how predaddy should be configured and used: https://github.com/szjani/predaddy-issuetracker-sample
