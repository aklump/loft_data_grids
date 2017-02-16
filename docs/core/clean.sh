#!/bin/bash
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
CORE="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# Remove all pattern dirs
for dir in $(find $CORE/install/patterns/*  -type d -maxdepth 1 ! -name "source" ); do
  dir="${dir##*/}"
  test -e $dir && rm -r "$dir"
done

# echo $CORE
# find $CORE/source -name _*
# return
# Remove compiled files in source; those that begin with underscore
for path in $(find "$CORE/../source" -name _*); do
    rm -rv "$path"
  # if [[ -f "$path" ]]; then
  #   rm -v "$path"
  # elif [[ -d "$path"]]; then
  # fi
done

