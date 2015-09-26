Scorpio SphinxSearch Library Change Log
=======================================

2015-09-26 0.2.0
----------------

Change "filters" in SearchIndex to "attributes". Made all properties paramters in
constructor so SearchIndex does not need to be overridden to set these.

Refactored SearchIndex to use an interface.


2015-09-25 0.1.0
----------------

Remove call to deprecated method setMatchMode. Now uses setRankingMode. Removed all
previous constants and references to MatchMode.

Change SearchManager to use ServerSettings and each query creates a new SphinxClient
instance removing the issues with bound queries not being removable and SphinxClient
not being cloneable.

2015-09-25
----------

Initial commit.