#!/usr/bin/bash
source ./gitflow.sh

gf_highlightEcho "你确定你是那个要负责合并代码的倒霉鬼么?[Y/n]"
gf_getChoice
if [ $CHOICE_RET == 'n' ]; then
    exit 0
fi

branch=$1
if [ ! -n "${branch}" ]
then
    gf_highlightEcho "请输入分支名称..."
    exit 0
fi

gf_merge $branch
