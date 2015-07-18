import gzip
import sys
import pymongo
import re
import urllib2
import os
from bs4 import BeautifulSoup
import json

def process_file(counts,fname):
 f = gzip.open(fname, 'r')

 for line in f:
  #counts.insert_one(page)
  try:
   l = line.split()
   tp = l[0].lower()
   if tp == 'en':
    title = re.sub(r'\_',r' ', l[1])
    title = re.sub(r'%28',r'(', title)
    title = re.sub(r'%29',r')', title)

    if re.match(r'(File|Category|Special|Talk):', title):
     continue
    if re.search(r'%\w\d%', title):
     continue

    count = int(l[2])
    if not title in counts:
     counts[title] = count
    else:
     counts[title] += count
  except:
   pass

 f.close()


def main():

 connection = pymongo.MongoClient()

 db = connection.wikiplaces

 counts = {}


 url = 'http://dumps.wikimedia.org/other/pagecounts-raw/2015/2015-05/'

 raw_page = urllib2.urlopen(url).read()
 soup = BeautifulSoup(raw_page)

 links = soup.select('a')

 for i in links:
  href = i['href']
  if re.search(r'pagecounts-2015050[1-5]', href) and os.path.isfile(href):
   print href
   process_file(counts,href)

 nerrors = 0

 for k,v in counts.iteritems():
  try:
   print k
   db.pages.update_one({"title" : k}, {"$set" : {"counts" :  v}})
  except:
   nerrors +=1

 print nerrors, ' database errors'


if __name__ == '__main__':
 main()



