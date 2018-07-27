#!/usr/bin/bash

OS_LINUX=1;
OS_OSX=2;
CURRENT_BRANCH=`git status | head -1 | cut -d " " -f3`
CHOICE_RET=''

gf_highlightEcho(){
    echo -e "\033[31m ${1} \033[0m"
}

gf_getChoice () {
	read choice
    c=${choice:0:1}
    while [ "$c" != 'Y' -a "$c" != 'n' ]; do
        echo "请输入: Y 或 n"
        read choice
        c=${choice:0:1}
    done
	CHOICE_RET=$c
	return 0
}

gf_checkBranch(){
    local branch=$1
    if [ "$branch" != "test" ] && [ "$branch" != "develop" ] && [ "$branch" != "feature" ] && [ "$branch" != "master" ]
    then
        echo "本自动化脚本仅支持分支: master,feature,test,develop"
        exit 1
    fi
}

gf_checkConflict(){
    DIRS=("src" "merge.sh" "pull.sh" "push.sh")
    for dir in ${DIRS[*]}; do
        echo "正在检测 $dir 是否存在冲突..."
        conflictFiles=`grep -rn '<<<<<<< HEAD' $dir`
        if [ -n "${conflictFiles}" ];
        then
            gf_highlightEcho "$dir 代码有冲突:"
            grep -rn '<<<<<<< HEAD' $dir
            exit 1
        fi
    done
}

gf_getOS() {
    matchStr=`uname | grep -i 'Linux'`
    if [ -n "${matchStr}" ]
    then
            return 1;
    fi

    matchStr=`uname | grep -i 'Darwin'`
    if [ -n "${matchStr}" ]
    then
            return 2;
    fi

    return 1;
}

gf_sed(){
    local pattern=$1
    local file=$2

    gf_getOS
    local os=$?
    if [ $os = $OS_OSX ];
    then
        sed -i '' "$pattern" $file
    else
        sed -i "$pattern" $file
    fi
}

gf_pull(){
    echo "更新远程源..."
    git remote update upstream

    echo "同步远程源分支 ${CURRENT_BRANCH} 至本地..."
    git pull upstream $CURRENT_BRANCH
}

gf_push(){
    echo "提交数据..."
    git push origin $CURRENT_BRANCH
}

gf_checkMerge(){
    from=$1

    case "$CURRENT_BRANCH" in
    "develop" )
        if [ $from != "test" ] && [ $from != "master" ];
        then
            gf_highlightEcho "本自动化脚本只允许从 'master'或'test' 合并至 develop"
            exit 1
        fi
        ;;

    "test" )
        if [ $from != "develop" ];
        then
            gf_highlightEcho "本自动化脚本只允许从 'develop' 合并至 test"
            exit 1
        fi
        ;;

    "feature" )
        if [ $from != "master" ];
        then
            gf_highlightEcho "本自动化脚本只允许从 'master' 合并至 feature"
            exit 1
        fi
        ;;
    "master" )
        if [ $from != "test" ] && [ $from != "feature" ];
        then
            gf_highlightEcho "本自动化脚本只允许从 'feature'或'test' 合并至 master"
            exit 1
        fi
        ;;
    * )
        ;;
    esac
}

gf_merge(){
    local from=$1

    gf_checkBranch $from
    gf_checkMerge $from

    gf_pull

    echo "确定要从 ${from} 分支合并到当前 ${CURRENT_BRANCH} 分支么?[Y/n]"
    gf_getChoice
    if [ $CHOICE_RET = 'n' ]; then
        exit 0
    fi

    if [ $CURRENT_BRANCH = "master" ];
    then
        git merge --squash $from
    else
        git merge $from
    fi

    echo "Merge 代码完成.按任意键继续..."
    read choice

    git status
}

# 必须检测当前分支,防止误操作
gf_checkConflict

gf_checkBranch $CURRENT_BRANCH
gf_highlightEcho "当前代码分支: ${CURRENT_BRANCH}"