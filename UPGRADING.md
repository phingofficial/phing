Upgrading from Phing 2.x to 3.0
===============================

This document aims to summarize all the breaking changes and noteworthy things
that you might stumble across when upgrading from Phing 2.x to 3.0.

* Omitting the `basedir` property in the root `project` tag now means "." instead
  of the current working directory. This effectively reverts the change made in 
  http://www.phing.info/trac/ticket/309 ([dfdb0bc](https://github.com/phingofficial/phing/commit/dfdb0bc8095db18284de364b421d320be3c1b6fb))
