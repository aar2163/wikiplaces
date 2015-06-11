import re
import pymongo
import json
import sys


connection = pymongo.MongoClient("mongodb://localhost")

db = connection.wikipedia

pages = db.pages

corpus = db.corpus.find_one()['corpus']


word = sys.argv[3]

index = corpus.index(word)

str_index = "revision.text_array." + str(index)

origin = [ float(sys.argv[2]), float(sys.argv[1]) ]

query = {"location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": 150000, "$minDistance": 0}}, str_index : 1}
#cursor = pages.find(query,{'title' : 1, 'location' : 1})
cursor = pages.find(query)


fname = word + '.json'

#output = open(fname, 'w')
entry = {}

n = 0
for i in cursor:
  lon,lat = i['location']['coordinates']
  title = i['title']
  title = title.encode('utf-8')
  entry[title] = {}
  entry[title]['lat'] = lat
  entry[title]['lon']  = lon
  underline = re.sub(r' ','_',title)
  url = 'http://en.wikipedia.org/wiki/' + underline
  entry[title]['url'] = url
  #print underline
  #print "{},{}".format(lat,lon)
  n +=1
  if(n > 10):
   break

#print n

print json.dumps(entry)
