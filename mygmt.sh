echo -e "Please input commit content? \c"
read answer
if  [ ! -n "$answer" ] ;then
    echo "Your commit content is empty!"
else
    git add .
    git commit -m "$answer"
fi