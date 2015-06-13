import sys

f = open(sys.argv[1],'r')
count = 0

print "<?xml version=\"1.0\"?>"
print "<mediawiki>"

for line in f:
 if(count > 4955000 and count < 4956000):
  print line.strip()
 if(count > 4956000):
  exit()
 count += 1

print "</mediawiki>"
