Sphinx Indexes
==============

Sphinx has to be told what indexes to create. This is done in the config file and two definitions are needed:

 * data source
 * index name / storage

See the sample sphinx.conf for an example "users" index that will make a fulltext index on keywords
and then load some parameters (attributes / filters) so we can refine searches for users.

Sphinx will need a MySQL user/password setting up with READ access and potentially CREATE TEMPORARY TABLE
if your source queries are complex.

Next we need to define the SearchIndexes for the SearchQuery to use.

SearchIndex
-----------

The SearchIndex class defines all the attributes and the index name so that we can easily use the Sphinx
index in the SearchManager.

The first step is to create a new class that extends SearchIndex. It is good practice to be consistent
with the naming e.g.: name the index after the Sphinx name using CamelCase, my_index => MyIndex or
active_user_index => ActiveUserIndex.

    class MyIndex extends \Scorpio\SphinxSearch\SearchIndex
    {
    }

Next we need to initialise the index with the available fields and attributes:

    class MyIndex extends \Scorpio\SphinxSearch\SearchIndex
    {

        protected function initialise()
        {
            // this should match the name of the index in the sphinx.conf
            $this->name = 'my_index';

            // available fields
            $this->availableFields = [
                'keywords',
            ];

            // indexed attributes for filtering
            $this->availableFilters = [
                'user_id', 'country', 'trust_worthiness', 'languages', 'tags',
            ];
        }
    }

Now we can run a search:

    use Scorpio\SphinxSearch;

    $sphinx  = new \SphinxClient('localhost', '9312');
    $manager = new SearchManager($sphinx);

    $query   = new SearchQuery(new MyIndex());
    $query->queryInFields('keywords', 'bob smith');

    $results = $manager->query($query);

If we wanted to search for anyone with a keyword containing "smi" we could run:

    use Scorpio\SphinxSearch;

    $sphinx  = new \SphinxClient('localhost', '9312');
    $manager = new SearchManager($sphinx);
    $index   = new MyIndex();
    $query   = new SearchQuery($index);
    $query->queryInFields('keywords', $index->createWildcardQueryString('smi'));

    $results = $manager->query($query);

"smi" will be automatically expanded to "*smi*".

Custom Result Objects
---------------------

By default all query results come back as ResultSet and ResultRecord entities. These
are general PHP objects that provide some light wrappers around the array formats returned
by the Sphinx extension.

To make the results handling easier these can be extended with custom methods and additional
functionality. For example: in our MyIndex we have several attributes, but right now we
have to access them via the getAttribute method of the ResultRecord. Instead it is often
desirable to customise this for each index.

This is done by extending the ResultRecord with a custom implementation and setting this
classname in the index:

    class MyIndexResultRecord extends \Scorpio\SphinxSearch\Result\ResultRecord
    {

        public function getUserId()
        {
            return $this->getAttribute('user_id');
        }

        public function getCountryId()
        {
            return $this->getAttribute('country_id');
        }

        public function getLanguages()
        {
            return $this->getAttribute('languages', []);
        }

        public function getLanguages()
        {
            return $this->getAttribute('tags', []);
        }
    }

Then in the search index:

    class MyIndex extends \Scorpio\SphinxSearch\SearchIndex
    {

        protected function initialise()
        {
            // the custom result class name
            $this->resultClass = MyIndexResultRecord::class;

            // ... the rest of the init
        }
    }

Now when the result set is iterated we will get instances of the MyIndexResultRecord.