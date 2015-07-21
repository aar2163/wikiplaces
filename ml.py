import pymongo
import dill
import re
import pandas as pd
import numpy as np
from scipy.sparse import vstack
from sklearn.feature_extraction.text import HashingVectorizer
import nltk
from sklearn.naive_bayes import MultinomialNB
from sklearn.linear_model import LogisticRegression
from sklearn.feature_selection import chi2, SelectKBest


def get_train():

 connection = pymongo.MongoClient()
 db = connection.wikiplaces



 #stored = db.queries.find_one({"query" : 'museum'})

 #query_vec = dill.loads(stored['dill'])

 vectorizer = dill.load(open('wiki_vectorizer-hashing.dill', 'r'))


 cursor = db.pages.find({"labels.museum" : {"$exists" : 1}})

 min_count = 1e10
 max_count = 0
 min_visits = 1e10
 max_visits = 0

 entry = {}

 vecs = []

 labels = []

 for i in cursor:
  entry_vec = dill.loads(i['revision']['text_array'])
  print i['title'],i['labels']['museum']
  lb = np.array(i['labels']['museum'][0])
  mlb = int(np.median(lb))
  labels.append([mlb])
  vecs.append(entry_vec)

 X = vstack(vecs)
 Y = np.array(labels)
 
 return X, Y

def main():

 X, Y = get_train()


 connection = pymongo.MongoClient()
 db = connection.wikiplaces

 vectorizer = dill.load(open('wiki_vectorizer-hashing.dill', 'r'))
 query_vec = vectorizer.transform(['museum']).transpose()


 #print vstack(vecs)

 #print 'alki', vecs[0]
 #print 'alki', vecs[1]


 sel = SelectKBest(chi2,k=10)
 Xp = sel.fit_transform(X,Y)
 print Xp.toarray()

 cf = MultinomialNB()

 cf = LogisticRegression()

 cf.fit(X,Y)

 origin = [ -73.9609, 40.8086 ]
 origin = [ 2.353841, 48.858577]

 query = {"location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": 10000, "$minDistance": 0}}}

 cursor = db.pages.find(query)

 vecs = []

 for i in cursor:
  entry_vec = dill.loads(i['revision']['text_array'])
  vecs.append(entry_vec)

  length = float(entry_vec.sum(axis=1)[0][0])
  count = float(entry_vec.dot(query_vec).toarray()[0][0])
  ratio = count/length

  if count < 4:
   continue
  print i['title']
  print cf.predict(entry_vec)

 

 X = vstack(vecs)

 #print cf.predict(X)

if __name__ == '__main__':
 main()

