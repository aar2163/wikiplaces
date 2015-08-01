import pymongo
import dill
import re
import numpy as np
from scipy.sparse import vstack
from sklearn.feature_extraction.text import HashingVectorizer
import nltk
from sklearn.naive_bayes import MultinomialNB
from sklearn.linear_model import LogisticRegression
from sklearn.feature_selection import chi2, SelectKBest
from sklearn.neural_network import BernoulliRBM
from sklearn.pipeline import Pipeline
import sys


def get_train(word):

 connection = pymongo.MongoClient()
 #connection = pymongo.MongoClient("mongodb://104.236.201.75")
 db = connection.wikiplaces



 #stored = db.queries.find_one({"query" : 'museum'})

 #query_vec = dill.loads(stored['dill'])


 labels_string = 'labels.' + word

 vectorizer = dill.load(open('wiki_vectorizer-hashing.dill', 'r'))


 cursor = db.pages.find({labels_string : {"$exists" : 1}})

 min_count = 1e10
 max_count = 0
 min_visits = 1e10
 max_visits = 0

 entry = {}

 vecs = []

 labels = []

 for i in cursor:
  entry_vec = dill.loads(i['revision']['text_array'])
  print i['title'].encode('utf-8'),i['labels'][word]
  #lb = np.array(i['labels'][word][0])
  lb = np.array(i['labels'][word])
  mlb = int(np.median(lb))
  labels.append([mlb])
  vecs.append(entry_vec)

 X = vstack(vecs)
 Y = np.array(labels)
 
 return X, Y

def main():

 word = sys.argv[1]

 X, Y = get_train(word)

 cf_string = 'classifier.' + word

 connection = pymongo.MongoClient()
 #connection = pymongo.MongoClient("mongodb://104.236.201.75")
 db = connection.wikiplaces

 vectorizer = dill.load(open('wiki_vectorizer-hashing.dill', 'r'))
 query_vec = vectorizer.transform([word]).transpose()


 #print vstack(vecs)

 #print 'alki', vecs[0]
 #print 'alki', vecs[1]


 sel = SelectKBest(chi2,k=500)
 Xp = sel.fit_transform(X,Y)
 print Xp.toarray()
 Xp = Xp.toarray()

 Xp[Xp > 1] = 1

 logistic = LogisticRegression()
 rbm = BernoulliRBM(random_state=0, verbose=True)

 classifier = Pipeline(steps=[('rbm', rbm), ('logistic', logistic)])
 #classifier = Pipeline(steps=[('logistic', logistic)])

 classifier.fit(Xp,Y)

 print classifier.predict(Xp)


 cf = MultinomialNB()

 cf = LogisticRegression()

 cf.fit(X,Y)

 origin = [ -73.984402, 40.752031 ]
 #origin = [ 2.353841, 48.858577]

 query = {"location": {"$near": {"$geometry": \
         {"type": "Point", "coordinates": origin}, \
          "$maxDistance": 10000, "$minDistance": 0}}}

 cursor = db.pages.find()

 #vecs = []

 for i in cursor:
  entry_vec = dill.loads(i['revision']['text_array'])
  #vecs.append(entry_vec)

  title = i['title']

  length = float(entry_vec.sum(axis=1)[0][0])
  count = float(entry_vec.dot(query_vec).toarray()[0][0])
  ratio = count/length


  if count < 4:
   continue


  Xp = sel.transform(entry_vec)
  Xp[Xp > 1] = 1

  #cl = cf.predict(entry_vec)[0]
  cl = classifier.predict(Xp)[0]
  if cl > 0:
   print title.encode('utf-8')
   print cl
  
  #db.pages.update_one({"title" : title}, {"$set" : {cf_string :  cl}})

 

 #X = vstack(vecs)

 #print cf.predict(X)

if __name__ == '__main__':
 main()

