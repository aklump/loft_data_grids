#!/bin/bash
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
CORE="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# Remove all pattern dirs, use the install footprint to determine the pattern directory names.
for dir in $(find $CORE/install/patterns/*  -type d -maxdepth 1 ! -name "source" ); do
  dir="${dir##*/}"

#  Todo This should use confirm when moving to cloudy.
  test -e $dir && rm -rv "$dir"
done

echo "Cleaned"
