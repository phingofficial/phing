Upgrading from Phing 2.x to 3.0
===============================

This document aims to summarize all the breaking changes and noteworthy things
that you might stumble across when upgrading from Phing 2.x to 3.0.

* Omitting the `basedir` property in the root `project` tag now means "." instead
  of the current working directory. This effectively reverts the change made in 
  http://www.phing.info/trac/ticket/309 (#668)
