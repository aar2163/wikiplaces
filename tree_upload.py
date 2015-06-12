import sys
import lxml.etree as ET
import re
import pymongo
import numpy as np
from sklearn.feature_extraction.text import CountVectorizer

words_list = ["climbing","hiking"]


def should_save(text):
 try:
  bSave = False
  for i in words_list:
   bSave = (bSave or re.search(i,text.text))
  return bSave
  #print text.text
 except:
  return False

def get_2coords(line):
 array = []
 for j in re.finditer(r'\|\w+',line):
  array.append(j.group(0)[1:])

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

 for j in re.finditer(r'=\s*-?\d+\.?\d*',line):
  s = j.group(0)[1:].strip()
  print 's',s
  try:
   deg.append(float(s))
  except:
   # Last element encodes the sign
   sign = get_sign(s)
   if (sign == None):
    return None

 print deg,line

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

def process_map(filename,pages, page_list):

 keys = {"lower": 0, "lower_colon": 0, "problemchars": 0, "other": 0}

 cursor = ET.iterparse(filename, events = ("start", "end"))

 event, root = cursor.next()

 ntotal = 0
 nsaved = 0

 chosen_title = 'Yosemite National Park'


 for event, element in cursor:
  if(element.tag == "page" and event == "end"):
   root.clear()  ## save memory

   ntotal += 1

   title = element.find("title")

   text = element.find("revision/text")

   bKeep = should_save(text)
   bKeep = True

   if not bKeep:
    continue

   txt = text.text

   try:
    txt = txt.encode('utf-8')
   except:
    txt = ' '
   lines = re.split(r'\n',txt)

   #if (title.text == chosen_title):
   # print txt
   # exit()
   #print title.text

   coord = lat = lon = None
   latline = lonline = ''
   for line in lines:

    # There are several different ways in which coordinates
    # are specified. get_coord is called when 
    # the format lat_d, lat_m, etc is used

    # these entries might also be in different lines, so 
    # accumulate first

    bLat = bLon = False

    if(title.text == chosen_title):
     print line

    if(re.match(r'\| lat_\w+\s*=',line)):
     latline = latline + line
     bLat = True
    if(re.match(r'\| long_\w+\s*=',line)):
     lonline = lonline + line
     bLon = True

    # get_2coords is for format 40|10|5|N|70|20|30|W

    if(re.match(r'\| coordinates\s*=',line)):
     lat,lon = get_2coords(line)

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
     #print coord
     break

   if coord:
    nsaved += 1
    page = make_dict(element)
    page['location'] = {'type': 'Point', 'coordinates': coord}

    #Insert document into MongoDB collection
    yield page['revision']['text']
    page['revision'].pop('text',None)
    page_list.append(page)
    print nsaved
    #if(nsaved == 10):
    # return 
    #pages.insert_one(page)


 print "uploaded:",nsaved
 print "total:",ntotal

 return



connection = pymongo.MongoClient()

db = connection.wikipedia

pages = db.pages

page_list = []

vocabulary = []

f = open('vocabulary.dat', 'r')

for line in f:
 vocabulary.append(line.strip()) 

#vectorizer = CountVectorizer( analyzer='word', stop_words = 'english', min_df = 0.005, max_df = 0.1, strip_accents = 'unicode', binary = True, dtype = np.int8 )
vectorizer = CountVectorizer( analyzer='word', stop_words = 'english', vocabulary = vocabulary, strip_accents = 'unicode', dtype = np.int8 )
X = vectorizer.fit_transform( process_map( sys.argv[1], pages, page_list ))


size = len(page_list)

nerror = 0

for i in range(size):
 page = page_list[i]
 page['revision']['text_array'] = X[i].toarray()[0].tolist()
 try:
  pages.insert_one(page)
 except:
  nerror +=1
 
print nerror,'database errors'

print X[0].toarray()[0]
print X.shape[0],len(page_list)
corpus = vectorizer.get_feature_names()

print len(corpus)


db.corpus.replace_one({}, {'corpus' : corpus}, upsert=True)

#process_map(sys.argv[1],pages)
