#!/bin/sh
if [ ! -f "$1" ]; then
    echo "Syntax: $0 spkexample"
    exit 1
fi
tar xzf "$1"
chmod a-x,o-w "INFO"
editor "INFO"
mv "$1" "$1~"
tar czf "$1" "INFO"
chmod a-x,o-w "$1"
rm "INFO"
