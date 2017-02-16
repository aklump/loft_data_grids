#!/usr/bin/env bash
# This file gets processed after an update.  It is broken out separate so that the new one is run after the core is rsynced.

# Warnings about breaking changes.
if [ "${get_version_return:0:3}" == "0.8" ]; then
    echo_yellow "Review CHANGELOG.txt for breaking changes in version 0.8"
fi

# Delete auto-generated.outline.json from source because it's now called outline.auto.inc as of 0.8.10
test -f "$docs_source_dir/auto-generated.outline.json" && rm "$docs_source_dir/auto-generated.outline.json"
