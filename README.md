# Wikiplaces

[Wikiplaces](http://wikiplaces.biz/) is a recommender system based on Wikipedia contents.

The entire English Wikipedia contents were downloaded as a 50 GB XML file. Pages describing locations were uploaded to a Mongo Database that can be queried with the results being shown to the user through the Google Maps API.

* **tree_upload.py** - Parsing the XML file and uploading records to the Mongo Database
* **tree_upload_mrjob.py** - Same thing, using Hadoop/MapReduce
* **query.py** - This script is called by the web interface and queries the Mongo Database, returning a list of pages associated with the chosen keyword.
* **index.php** - Main PHP code. Results from query.py are sorted and shown to the user on a Google Maps layer (Javascript).

