import re
import pymongo
import json
import sys
import dill
import os
import nltk
from bson.binary import Binary
from sklearn.feature_extraction.text import CountVectorizer

def get_vectorized(db, word):

 word = word.lower()

 stored = db.queries.find_one({"query" : word})

 if not stored:
  vectorizer = dill.load(open('wiki_vectorizer.dill', 'r'))

  query_vec = vectorizer.transform([word]).transpose()

  dumps = dill.dumps(query_vec)
  
  db.queries.insert_one({"query" : word, \
                         "dill" : Binary(dumps)})
 else:
  query_vec = dill.loads(stored['dill'])

 return query_vec


#connection = pymongo.MongoClient("mongodb://localhost")
connection = pymongo.MongoClient("mongodb://104.236.201.75")

default_tokenizer = CountVectorizer().build_tokenizer()
stemmer = nltk.stem.SnowballStemmer("english", ignore_stopwords=True)


db = connection.wikipedia

pages = db.pages




word = sys.argv[3]



origin = [ float(sys.argv[2]), float(sys.argv[1]) ]

distance = float(sys.argv[4])*1000


nmin = 1

"""
query = {"location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": distance, "$minDistance": 0}}, str_index : {"$gt" : nmin}}

query = {str_index : {"$gt" : nmin}, \
         "location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": distance, "$minDistance": 0}}}
"""

query = {"location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": distance, "$minDistance": 0}}}

"""
projection = {"title" : 1, \
              "revision.text_array" : {"$slice" : [index,1]}, \
              "location" : 1}
"""


geonear  = {"$geoNear": {"near":  {"type": "Point", "coordinates": origin}, \
            "maxDistance": distance,  \
            "distanceField" : "dist", \
            "spherical" : True}}

unwind = {"$unwind" : "$revision.text_array"}

#cursor = pages.find(query,{'title' : 1, 'location' : 1})
#cursor = pages.find(query, projection)
cursor = pages.find(query)

#cursor = pages.aggregate([geonear, unwind])


fname = word + '.json'

#output = open(fname, 'w')
entry = {}


query_vec = get_vectorized(db, word)

min_count = 1e10
max_count = 0
min_visits = 1e10
max_visits = 0


n = 0
for i in cursor:
  #print i['revision']['text_array']
  lon,lat = i['location']['coordinates']
  entry_vec = dill.loads(i['revision']['text_array'])
  count = float(entry_vec.dot(query_vec).toarray()[0][0])

  if count < 4:
   continue

  try:
   visits = float(i['counts'])
  except:
   visits = 0.0


  if visits < min_visits:
   min_visits = visits
  if visits > max_visits:
   max_visits = visits
  if count < min_count:
   min_count = count
  if count > max_count:
   max_count = count

  title = i['title']


  title = title.encode('utf-8')
  title = re.sub(r'(?<!\\)\'',r'\'',title)
  #print title
  entry[title] = {}

  if re.search(' ' + word + ' ', title.lower()):
   entry[title]['title_match'] = 1.0
  else:
   entry[title]['title_match'] = 0.0

  entry[title]['lat'] = lat
  entry[title]['lon']  = lon
  #entry[title]['count'] = int(i['revision']['text_array'][0])
  entry[title]['count'] = count 
  entry[title]['visits'] = visits
  underline = re.sub(r' ','_',title)
  url = 'http://en.wikipedia.org/wiki/' + underline
  url = re.sub(r'(?<!\\)\'',r'\'',url)
  #print url
  entry[title]['url'] = url
  #print underline
  #print "{},{}".format(lat,lon)
  n +=1
  #if(n > 10):
  # break

print 'cheguei'

#print n

delta_count = max_count - min_count
delta_visits = max_visits - min_visits
for i in entry:
 e = entry[i]
 e['score'] = 10*(e['count']-min_count)/delta_count + \
              1*(e['visits']-min_visits)/delta_visits + \
              10*e['title_match']

print json.dumps(entry)

