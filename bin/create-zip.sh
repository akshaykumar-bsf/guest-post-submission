#!/bin/bash

# Exit if any command fails
set -e

# Get plugin version from the main plugin file
VERSION=$(grep -o "Version: .*" guest-post-submission.php | sed 's/Version: //')

# If version is empty, use package.json version
if [ -z "$VERSION" ]; then
  VERSION=$(node -p "require('./package.json').version")
fi

# Create a clean build directory
echo "Creating build directory..."
rm -rf ./build
mkdir -p ./build/guest-post-submission

# Copy only the necessary files to the build directory
echo "Copying files..."
cp -R ./admin ./build/guest-post-submission/
cp -R ./includes ./build/guest-post-submission/
cp -R ./languages ./build/guest-post-submission/
cp -R ./public ./build/guest-post-submission/
cp -R ./templates ./build/guest-post-submission/
cp -R ./assets ./build/guest-post-submission/

# Copy individual files
cp ./guest-post-submission.php ./build/guest-post-submission/
cp ./uninstall.php ./build/guest-post-submission/
cp ./readme.txt ./build/guest-post-submission/
cp ./README.md ./build/guest-post-submission/

# Create the zip file
echo "Creating zip file..."
cd ./build
zip -r guest-post-submission-$VERSION.zip ./guest-post-submission
mv guest-post-submission-$VERSION.zip ../
cd ..

# Clean up
echo "Cleaning up..."
rm -rf ./build

echo "✅ Successfully created guest-post-submission-$VERSION.zip"
