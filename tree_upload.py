import sys
import lxml.etree as ET
import re
import pymongo
import numpy as np
from bson.binary import Binary
from sklearn.feature_extraction.text import CountVectorizer, HashingVectorizer
import dill
import nltk


def get_2coords(line):
 array = []
 for j in re.findall(r'\|\s*([0-9NEWS]+)',line):
  array.append(j)

 lat = lon = None

 deg = []
 for i in [0,1,2]:
  try:
   deg.append(float(array[i]))
  except:
   pass
 try:
  sign = get_sign(array[3])
  lat = deg_to_decimal(deg,sign)
 except:
  return None,None

 deg = []
 for i in [4,5,6]:
  try:
   deg.append(float(array[i]))
  except:
   pass
 try:
  sign = get_sign(array[7])
  lon = deg_to_decimal(deg,sign)
 except:
  return None,None


 return lat,lon
 

def get_coord(line):
 deg = []
 sign = 1

 for j in re.findall(r'(?:long|lat)\_\w+\s*=\s*([0-9EWNS]+)',line):
  s = j
  try:
   deg.append(float(s))
  except:
   # Last element encodes the sign
   sign = get_sign(s)
   if (sign == None):
    return None

 #print deg,line

 if(len(deg) == 0):
  return None

 coord = deg_to_decimal(deg,sign)

 return coord

def get_sign(s):
 if(s == "S" or s == "W"):
  sign = -1
 elif(s == "N" or s == "E"):
  sign = 1
 else:
  sign = None
 return sign

def deg_to_decimal(deg,sign):
 coord = deg[0] 

 #small trick for dealing with degrees and decimals
 for i in [1,2]:
  try: 
   coord += deg[i]/pow(60,i)
  except:
   pass

 return sign*coord

def make_dict(element):
 dic = {}

 for i in element:
  if(len(i) == 0):
   dic[i.tag] = i.text
  else:
   dic[i.tag] = make_dict(i)

 return dic

def get_2coords_float(line):
 g = re.findall(r'(\d+\.\d+)\|([NSEW])', line)
 lat = float(g[0][0])*get_sign(g[0][1])
 lon = float(g[0][0])*get_sign(g[0][1])
 return lat, lon

def process_coord(lines):
   coord = lat = lon = None
   latline = lonline = ''

   for line in lines:

    # There are several different ways in which coordinates
    # are specified. get_coord is called when 
    # the format lat_d, lat_m, etc is used

    # these entries might also be in different lines, so 
    # accumulate first

    bLat = bLon = False


    elat = re.findall(r'latitude\s*=\s*([\d\.\-]+)', line)
    elon = re.findall(r'longitude\s*=\s*([\d\.\-]+)', line)

    if elat:
     try:
      lat = float(elat[0])
      bLat = True
     except:
      pass
    if elon:
     try:
      lon = float(elon[0])
      bLon = True
     except:
      pass

    if(re.match(r'\| lat_\w+\s*=',line)):
     latline = latline + line
     bLat = True
    if(re.match(r'\| long_\w+\s*=',line)):
     lonline = lonline + line
     bLon = True

    # get_2coords is for format 40|10|5|N|70|20|30|W

    if re.search(r'\d+\|\d+\|\d+\|[NS]\|\d+\|\d+\|\d+\|[EW]', line):
     lat,lon = get_2coords(line)
    if re.search(r'\d+\.\d+\|[NS]\|\d+\.\d+\|[EW]', line):
     lat,lon = get_2coords_float(line)
     

    # Now call get_coord
    if(re.search(r'lat_NS\s*=',line)):
     lat = get_coord(latline)
    if(latline != '' and lat == None and not bLat and not bLon):
     lat = get_coord(latline)
    if(re.search(r'long_EW\s*=',line)):
     lon = get_coord(lonline)
    if(lonline != '' and lon == None and not bLat and not bLon):
     lon = get_coord(lonline)

    if(lat and lon):
     coord = [lon,lat]
     print coord
     return coord

def process_map(filename,pages, page_list):

 keys = {"lower": 0, "lower_colon": 0, "problemchars": 0, "other": 0}

 cursor = ET.iterparse(filename, events = ("start", "end"))

 event, root = cursor.next()

 ntotal = 0
 nsaved = 0


 for event, element in cursor:
  if(element.tag == "page" and event == "end"):
   root.clear()  ## save memory

   ntotal += 1

   title = element.find("title")

   text = element.find("revision/text")

   txt = text.text

   try:
    txt = txt.encode('utf-8')
   except:
    txt = ' '
   lines = re.split(r'\n',txt)

   coord = process_coord(lines)



   if coord:
    nsaved += 1
    page = make_dict(element)
    #print page['title']
    page['location'] = {'type': 'Point', 'coordinates': coord}

    #Insert document into MongoDB collection
    yield page['revision']['text']
    page['revision'].pop('text',None)
    page_list.append(page)
    print nsaved


 print "uploaded:",nsaved
 print "total:",ntotal

 return

def tokenize_stem(text):
    """
    We will use the default tokenizer from TfidfVectorizer, combined with the nltk SnowballStemmer.
    """
    default_tokenizer = HashingVectorizer().build_tokenizer()
    tokens = default_tokenizer(text)
    stemmer = nltk.stem.SnowballStemmer("english", ignore_stopwords=True)
    stemmed = map(stemmer.stem, tokens)
    return stemmed


def main():

 connection = pymongo.MongoClient()

 db = connection.wikiplaces

 pages = db.pages

 page_list = []

 vectorizer = HashingVectorizer(stop_words = nltk.corpus.stopwords.words('english'), tokenizer=tokenize_stem)

 X = vectorizer.fit_transform( process_map( sys.argv[1], pages, page_list ))

 f = open('wiki_vectorizer-hashing.dill', 'w')
 dill.dump(vectorizer, f)
 f.close()

 size = len(page_list)

 nerror = 0


 for i in range(size):
  page = page_list[i]
  out = dill.dumps(X[i])
  page['revision']['text_array'] = Binary(out)
  try:
   pages.insert_one(page)
  except:
   nerror +=1
 
 print nerror,'database errors'

if __name__ == '__main__':
 main()

