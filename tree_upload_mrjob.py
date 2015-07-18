#!/usr/bin/python
# Copyright 2009-2010 Yelp
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
"""The classic MapReduce job: count the frequency of words.
"""
from mrjob.job import MRJob
from mrjob.step import MRStep
import re
import heapq
import mwparserfromhell as mwp
import lxml.etree as ET
from tree_upload import process_coord, make_dict
import nltk
import dill
from bson.binary import Binary
from sklearn.feature_extraction.text import HashingVectorizer
import pymongo

WORD_RE = re.compile(r"[\w']+")


class MRWordFreqCount(MRJob):

    def steps(self):
        return [MRStep(mapper_init=self.init_mapper,
                       mapper=self.the_mapper)]

    def init_mapper(self):
     self.ispage = False
     self.string = ''
     self.nsaved = 0
     self.nerror = 0
     self.connection = pymongo.MongoClient()

     self.db = self.connection.wikiplaces_mr

     self.pages = self.db.pages

     self.vectorizer = HashingVectorizer(stop_words = nltk.corpus.stopwords.words('english'), tokenizer=tokenize_stem)

    def the_mapper(self, _, line):
     if re.search(r'<page>', line):
      self.ispage = True

     if self.ispage:
      self.string += line


     #print line, len(self.string)
     if re.search(r'</page>', line) and len(self.string) > 0:
      st = self.string
      #print 'aki', st
      tree = ET.fromstring(st)

      title = tree.find("title")
      text = tree.find("revision/text")

      txt = text.text

      try:
       txt = txt.encode('utf-8')
      except:
       txt = ' '

      lines = re.split(r'\n',txt)
      coord = process_coord(lines)

      if coord:
       page = make_dict(tree)

       self.nsaved += 1
       print self.nsaved

       #print page['title']
       page['location'] = {'type': 'Point', 'coordinates': coord}

       #Insert document into MongoDB collection
       tvec = self.vectorizer.transform(page['revision']['text'])
       out = dill.dumps(tvec)

       page['revision'].pop('text',None)
       page['revision']['text_array'] = Binary(out)

       try:
        self.pages.insert_one(page)
       except:
        self.nerror +=1
        print 'nerror', self.nerror

      self.ispage = False
      self.string = ''

def tokenize_stem(text):
    """
    We will use the default tokenizer from TfidfVectorizer, combined with the nltk SnowballStemmer.
    """
    default_tokenizer = HashingVectorizer().build_tokenizer()
    tokens = default_tokenizer(text)
    stemmer = nltk.stem.SnowballStemmer("english", ignore_stopwords=True)
    stemmed = map(stemmer.stem, tokens)
    return stemmed

def parse_text(txt):
 print 'aki', unicode(txt)



if __name__ == '__main__':
    MRWordFreqCount.run()
