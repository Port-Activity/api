#!/bin/sh
DIR="src/lib/SMA/PAA/SERVICE/unlocode/"
FILE="$DIR/code-list.csv"
cat $FILE | sed 's/^[+¦],/,/' | cut -d, -f2 | uniq | while read delim; do grep -e "^,$delim," -e "^[+¦],$delim," $FILE > $DIR/code-list-$delim.csv; done;
