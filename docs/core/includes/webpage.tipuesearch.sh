#!/bin/bash
# 
# @file
# Adds search functionality to to a webpage.
# 
source_dir=$1
output_dir=$2

mkdir -p "$output_dir"
rsync -a "$source_dir/search/tipuesearch/" "$output_dir/" --delete
