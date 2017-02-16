#!/usr/bin/env bash
function echo_aqua() {
    echo "`tput setaf 6`$1`tput op`"
}

function echo_purple() {
    echo "`tput setaf 5`$1`tput op`"
}

function echo_blue() {
    echo "`tput setaf 4`$1`tput op`"
}

function echo_yellow() {
    echo "`tput setaf 3`$1`tput op`"
}

function echo_red() {
    echo "`tput setaf 1`$1`tput op`"
}

function echo_green() {
    echo "`tput setaf 2`$1`tput op`"
}

#
# Return the full path to the version file
#
#
function get_version_file() {
  local file
  file=${docs_version_file[0]}
  if [ ! "$file" ]; then
    return 1
  fi

  # The path starts with a / it is absolute
  if [[ "${file:0:1}" == '/' ]]; then
    file="$file";
  else
    # Resolve the file relative to source.
    file=$(realpath "$docs_root_dir/$file");
  fi

  if test -f "$file"; then
      echo $file
      return 0
  fi
  return 1
}

# Usage
# result=$(func_name arg)

#
# Call the version hook and return the version string
get_version_return=''
function get_version() {
  local hook=$(realpath "$CORE/${docs_version_hook[0]}")
  if [ "$hook" ] && [ -f "$hook" ]; then
    get_version_return=$(do_hook_file "$hook")
    if [[ "$get_version_return" ]]; then
      echo_blue "Current version is: $get_version_return"
    fi
  fi

  if [[ ! "$get_version_return" ]]; then
    get_version_return='1.0'
    echo_yellow "Using default version $get_version_return."
  fi
}

#
# Return the realpath
#
# @param string $path
#
function realpath() {
  local path=$($docs_php "$CORE/includes/realpath.php" "$1")
  echo $path
}


##
 # Load the configuration file
 #
 # Lines that begin with [ or # will be ignored
 # Format: Name = "Value"
 # Value does not need wrapping quotes if no spaces
 # File MUST HAVE an EOL char!
 #
function load_config() {

  # defaults
  docs_php=$(which php)
  docs_bash=$(which bash)
  docs_lynx=$(which lynx)
  docs_source_dir='source'
  docs_root_dir=$(realpath "$CORE/..")
  docs_source_path=$(realpath "$docs_root_dir/$docs_source_dir")
  docs_plugins_tpl='twig'
  docs_plugins_theme='twig'
  docs_partial_extension='.md'
  docs_markdown_extension='.md'
  docs_kit_dir='kit'
  docs_website_dir='public_html'
  docs_html_dir='html'
  docs_mediawiki_dir='mediawiki'
  docs_text_dir='text'
  docs_drupal_dir='advanced_help'
  docs_cache_dir="$CORE/cache"
  docs_tmp_dir="$docs_cache_dir/tmp"
  docs_todos="_tasklist$docs_markdown_extension"
  docs_version_hook='version_hook.php'
  docs_pre_hooks=''
  docs_post_hooks=''
  docs_outline_auto='outline.auto.json'
  docs_outline_merge='outline.merge.json'

  # Check for installation if needed.
  if [ ! -f core-config.sh ]; then
    echo_yellow "Installing..."
    cp "$CORE/install/core-config.sh" "$docs_root_dir/"
    installing=1
  fi

  # Installation steps
  test -d "$docs_source_path/" || rsync -a "$CORE/install/source/" "$docs_source_path/"

  #
  #
  # Discover the outline file
  #
  if test -e "$docs_cache_dir/$docs_outline_auto"; then
    rm "$docs_cache_dir/$docs_outline_auto"
  fi

  # We're looking ultimately for outline.json
  docs_outline_file=$(find $docs_source_path -name outline.json)

  # If it's not there we'll try to generate from a .ini file.
  if [[ ! "$docs_outline_file" ]]; then
    # Ini file
    docs_help_ini=$(find $docs_source_path -name *.ini)

    if [[ "$docs_help_ini" ]]; then
      # Convert this to $docs_outline_auto
      $docs_php "$CORE/includes/ini_to_json.php" "$docs_help_ini" "$docs_source_path" "$docs_source_path/$docs_outline_auto"
      docs_outline_file="$docs_cache_dir/$docs_outline_auto"

      echo "`tty -s && tput setaf 3`You are using the older .ini version of the configutation; consider changing to outline.json, a template has been created for you as '$docs_outline_auto'.  See README for more info.`tty -s && tput op`"
    fi
  fi

  # custom
  parse_config core-config.sh

  # Create any necessary directories
  mkdir -p "$docs_cache_dir/source/"

  #
  # put anything that comes AFTER parsing config file below this line
  #

  # Determine which is our tpl dir
  if test -e "$PWD/tpl"; then
    docs_tpl_dir="$PWD/tpl"
  else
    docs_tpl_dir=$(get_plugin_path $docs_plugins_theme theme)
  fi

  docs_text_enabled=1
  if ! lynx_loc="$(type -p "$docs_lynx")" || [ -z "$lynx_loc" ]; then
    echo "`tput setaf 3`Lynx not found; .txt files will not be created.`tput op`"
    docs_text_enabled=0
  fi

  # Below this line, anything that is dependent upon $docs_root_dir which can
  # be overridden by the config file
  if [[ ! "$docs_hooks_dir" ]]; then
    docs_hooks_dir="$docs_root_dir/hooks"
  fi

  if [[ ! "$docs_version_file" ]]; then
    docs_version_file="$docs_root_dir/*.info"
  fi
  docs_version_file="$(get_version_file)"

  docs_disabled=($docs_disabled)
}

##
 # Parse a config file
 #
 # @param string $1
 #   The filepath of the config file
 #
function parse_config() {
  if [ -f $1 ]
  then
    while read line; do
      if [[ "$line" =~ ^[^#[]+ ]]; then
        name=${line% =*}
        value=${line##*= }
        if [[ "$name" ]]
        then
          eval docs_$name=$value
        fi
      fi
    done < $1
  fi
}

#
# Execute a .sh or .php hook file
#
# @param string $file
#
function do_hook_file() {
  local file=$1
  if [[ ${file##*.} == 'php' ]]; then
    cmd="$docs_php"
  elif [[ ${file##*.} == 'sh' ]]; then
    cmd=$docs_bash
  fi

  if [[ ! -f $file ]]; then
    echo "`tput setaf 1`Hook file not found: $file`tput op`"
  elif [[ "$cmd" ]]; then
    $cmd "$file" "$docs_source_path" "$CORE" "$docs_version_file" "$docs_root_dir"
    # echo $($cmd "$file" "$source" "$CORE" "$docs_root_dir/$docs_version_file")
  fi
}

#
# Do the pre-compile hook
#
function do_pre_hooks() {
    local hook

    # Hack to fix color, no time to figure out 2015-11-14T13:58, aklump
    #  echo "`tty -s && tput setaf 6``tty -s && tput op`"
    echo "Running pre-compile hooks..."
    for hook in ${docs_pre_hooks[@]}; do
        hook=$(realpath "$docs_hooks_dir/$hook")
        echo_green "Hook file: $hook"
        echo_yellow $(do_hook_file $hook)
    done

    # Empty out the page data cache.
    test -e "$docs_cache_dir/page_data.json" && rm "$docs_cache_dir/page_data.json"

    # Internal pre hooks should always come after the user-supplied
    do_todos

    # Generate an outline from the file structure
    if [[ ! "$docs_outline_file" ]]; then
        # Create $docs_outline_auto from the file contents
        $docs_php "$CORE/includes/files_to_json.inc" "$docs_source_path" "$docs_cache_dir/source" "$docs_cache_dir/$docs_outline_auto" "$docs_source_dir/$docs_outline_merge"

        docs_outline_file="$docs_cache_dir/$docs_outline_auto"
    fi
}

#
# Do the todo item gathering
#
function do_todos() {
  if [[ "$docs_todos" ]]; then
    local global="$docs_cache_dir/source/$docs_todos"
    echo "Aggregating todo items..."

    if [[ ! -f "$global" ]]; then
      touch "$global";
    fi

    for file in $(find $docs_source_dir -type f -iname "*$docs_markdown_extension"); do
      if [ "$file" != "$global" ]; then
        # Send a single file over for processing todos via php
        $docs_php "$CORE/includes/todos.inc" "$file" "$global"
      fi
    done
  fi
}

#
# Do the post-compile hook
#
function do_post_hooks() {
  local hook
  echo "Running post-compile hooks..."
  for hook in ${docs_post_hooks[@]}; do
    hook=$(realpath "$docs_hooks_dir/$hook")
    echo "`tty -s && tput setaf 2`Hook file: $hook`tty -s && tput op`"
    echo $(do_hook_file $hook)
  done

  # Internal post hooks should always come after the user-supplied
  # Remove the _tasklist.md file
  ! test -e "$docs_source_dir/$docs_todos" || rm "$docs_source_dir/$docs_todos"
}

#
# Source the correct file for a plugin operation
#
# @param string plugin machine name e.g. twig
# @param string operation e.g. file|post
#
function do_plugin_handler() {
    local hook_file
    hook_file=$($docs_php $CORE/includes/plugins.php "$CORE/plugins/" "$1" "$2")
    test -f "$hook_file" && source "$hook_file"
}

function get_plugin_path() {
    local hook_file
    hook_file=$($docs_php $CORE/includes/plugins.php "$CORE/plugins/" "$1" "$2")
    echo $hook_file
}

##
 # End execution with a message
 #
 # @param string $1
 #   A message to display
 #
function end() {
  echo
  echo $1
  echo
  exit;
}

##
 # Checks to see if a file was generated and displays a message
 #
 # @param string $1
 #   filename to check
 #
 # @return NULL
 #   Sets the value of global $func_name_return
 #
function _check_file() {
  if ! test -f "$1"; then
    echo_red "Failed generating $1"
  fi
}

##
 # Determine if an output format is enabled
 #
 # @param string $1
 #   The output format to check e.g., 'html'
 #
 # @return 0|1
 #
function is_disabled() {
  local seeking=$1
  local in=1
  for element in "${docs_disabled[@]}"; do
   if [[ $element == $seeking ]]; then
     in=0
     break
   fi
  done
  return $in
}
