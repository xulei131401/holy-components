#!/usr/bin/bash
# 注意这里不要用https的方式地址，否则每次都需要输入账号和密码
REPOSITORY=https://github.com/xulei131401/holy-helper.git;
gf_init () {
    echo '初始化仓库'
    git config --global credential.helper store #防止每次push都会输入账号和密码
    git init
}

gf_remote_add () {
    git remote add origin REPOSITORY #这里是远程仓库地址
    git add .
    git commit
    git push -u origin master
}

gf_checkout_branch () {
    git checkout -b develop origin/develop
}

gf_remote_track () {
	git remote add upstream REPOSITORY #这里是远程仓库地址
	git remote update upstream
	git branch -a
	git chekout develop
    git remote update upstream
    git pull upstream develop
}

gf_start () {
	gf_init
	gf_remote_add
	gf_checkout_branch
	gf_remote_track
}

gf_start