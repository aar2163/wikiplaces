import sys

f = open(sys.argv[1],'r')
count = 0

print "<?xml version=\"1.0\"?>"
print "<mediawiki>"

for line in f:
 if(count > 66600000 and count < 66610000):
  print line.strip()
 if(count > 66610000):
  exit()
 count += 1

print "</mediawiki>"
