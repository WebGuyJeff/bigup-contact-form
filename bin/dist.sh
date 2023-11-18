# !/usr/bin/env bash

# Function: Convert a string to title case.
prettyTitle() {
    sed 's/.*/\L&/; s/[a-z]*/\u&/g' <<<"$1"    
}

BUILD_VERSION=$(node -pe "require('./package.json').version")
export BUILD_VERSION
BUILD_NAME=$(node -pe "require('./package.json').name")
export BUILD_NAME
PRETTY_BUILD_NAME=$(prettyTitle "$BUILD_NAME")

# ZIP_COMMENT must end in "."
ZIP_COMMENT="This is the $PRETTY_BUILD_NAME WordPress Theme."

if [ ! -d "dist" ]; then
  mkdir "dist"
fi

if [ ! -z "$1" ] && [ $1 == 'development' ]; then
  ZIP_NAME="$BUILD_NAME-dev"
else
  ZIP_NAME=$BUILD_NAME
fi

# Take all the files, filter the dev ones (e.g. node_modules, src), and save the result to './dist'
rsync -rc --files-from ".distinclude" --exclude-from ".distexclude" "./" "dist/$BUILD_NAME"

cd dist

# Create a zip file in './dist' from the filtered files in './dist/temp'
echo "$ZIP_COMMENT" | zip -r -z "./$ZIP_NAME" "./$BUILD_NAME"

rm -r $BUILD_NAME

echo "$PRETTY_BUILD_NAME build success! 🥳"

cd -

exit 0