#!/bin/bash
#
# @file
# Update the core loft_docs files
#
# USAGE:
# 1. Make this file executable
# 2. Backup your project
# 3. ./update.sh
# 4. Test your project
#
# CREDITS:
# In the Loft Studios
# Aaron Klump - Web Developer
# PO Box 29294 Bellingham, WA 98228-1294
# aim: theloft101
# skype: intheloftstudios
#
#
# LICENSE:
# Copyright (c) 2013, In the Loft Studios, LLC. All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
#   1. Redistributions of source code must retain the above copyright notice,
#   this list of conditions and the following disclaimer.
#
#   2. Redistributions in binary form must reproduce the above copyright notice,
#   this list of conditions and the following disclaimer in the documentation
#   and/or other materials provided with the distribution.
#
#   3. Neither the name of In the Loft Studios, LLC, nor the names of its
#   contributors may be used to endorse or promote products derived from this
#   software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY IN THE LOFT STUDIOS, LLC "AS IS" AND ANY EXPRESS
# OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
# EVENT SHALL IN THE LOFT STUDIOS, LLC OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
# INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
# OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
# NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
# EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
# The views and conclusions contained in the software and documentation are
# those of the authors and should not be interpreted as representing official
# policies, either expressed or implied, of In the Loft Studios, LLC.
#
#
# @ingroup loft_docs
# @{
#
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
CORE="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
ROOT="$CORE/.."

source $CORE/functions.sh

# Override to use the version file of the core
function get_version_file() {
    echo $ROOT/core-version.info
}

load_config

if [ ! -d "$ROOT/core" ] && [ ! -f "$ROOT/core_version.info" ]; then
  echo_red "Update failed. Corrupt file structure."
  exit
fi

if [ -d "$ROOT/tmp" ]; then
  echo_red "You must delete $ROOT/tmp before updating."
  exit
else
  mkdir -p "$ROOT/tmp"
fi

# Get's the before version
get_version
before_version=$get_version_return

echo_green "Downloading current release..."

cd "$ROOT/tmp"

# Download the master branch
curl -O -L https://github.com/aklump/loft_docs/archive/master.zip
unzip -q master.zip;
cd "$ROOT"


# Update the core files
docs_update="$ROOT/tmp/loft_docs-master/"

cp "$docs_update/README$docs_markdown_extension" "$ROOT/"
cp "$docs_update/core-version.info" "$ROOT/"

# Update all of core
rsync -a --delete "$docs_update/core/" "$ROOT/core/"

## By putting this after rsync, we will retrieve the new version
get_version

## Allow the incoming update to do work.
source "$docs_update/core/update_finalize.sh"

rm -rf "$ROOT/tmp"

echo_green "Updated complete"
