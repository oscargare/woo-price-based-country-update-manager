#!/bin/bash
grep -rno -E --include=\*.php 'print_log|var_log|cart_item_log' . && exit 1

cd ..
task=$1
slug="$(basename ${PWD})"
plugin_file="./${slug}.php"

if [ -z "$task" ]
then
	task="deploy"
fi

if [ $task = "deploy" ] || [ $task = "pot" ]
then
    echo "Running (pot) task"
	#text domain
	text_domain=$(cat ${plugin_file} | grep "Text Domain:" | cut -d ":" -f 2 | sed -e 's/^[ \t]*//')
	#pot file.
	wp i18n make-pot . languages/${text_domain}.pot
fi

if [ $task = "deploy" ] || [ $task = "zip" ]
then
	echo "Running (zip) task"
	#zip
	rm -rf ./dist/*
	wp dist-archive ../${slug}/ --filename-format="{name}" ../${slug}/dist/

fi

