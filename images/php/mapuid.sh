#!/usr/bin/env bash
set -e

# Change uid and gid of www-data to match current dir's owner
uid=$(stat -c '%u' "$PWD")
gid=$(stat -c '%g' "$PWD")

usermod -u "$uid" www-data 2> /dev/null && {
  groupmod -g "$gid" www-data 2> /dev/null || usermod -a -G "$gid" www-data
}
