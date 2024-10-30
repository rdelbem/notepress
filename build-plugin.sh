#!/bin/bash

PLUGIN_NAME="notepress"
BUILD_DIR="build"
ZIP_NAME="${PLUGIN_NAME}.zip"

echo "Cleaning up previous build directory and previous zip file..."
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"

if [ -f "composer.json" ]; then
  echo "Installing Composer dependencies..."
  composer install --no-dev --optimize-autoloader
fi

if [ -f "package.json" ]; then
  echo "Installing npm dependencies and building assets..."
  npm install
  npm run build || echo "No build script found. Skipping asset build."
  rm -rf node_modules
fi

echo "Creating build directory..."
mkdir "$BUILD_DIR"

echo "Copying files to build directory..."
rsync -av . "$BUILD_DIR" --exclude "node_modules" \
  --exclude ".git" \
  --exclude ".github" \
  --exclude ".nvmrc" \
  --exclude "__mocks__" \
  --exclude "react-app" \
  --exclude "wordpress" \
  --exclude "docker" \
  --exclude "tests" \
  --exclude ".babelrc" \
  --exclude ".env" \
  --exclude ".env.testing" \
  --exclude ".gitignore" \
  --exclude "codeception.yml" \
  --exclude "convertToTypescript.ts" \
  --exclude "docker-compose.yml" \
  --exclude "global.d.ts" \
  --exclude "jest.config.js" \
  --exclude "jest.setup.ts" \
  --exclude "package-lock.json" \
  --exclude "package.json" \
  --exclude "README.md" \
  --exclude "tsconfig.json" \
  --exclude "webpack.config.js" \
  --exclude "wp-config.php" \
  --exclude "src/Types/generate-contracts.php" \
  --exclude "src/WPCLI" 

if [ ! -d "$BUILD_DIR" ]; then
  echo "Error: Build directory was not created. Exiting."
  exit 1
fi

echo "Removing unnecessary files from build directory..."
rm -f "$BUILD_DIR/build-plugin.sh"

cd "$BUILD_DIR" || exit

echo "Creating zip file..."
zip -r "../$ZIP_NAME" . || { echo "Error: Failed to create ZIP file"; exit 1; }

cd ..

echo "Cleaning up build directory..."
rm -rf "$BUILD_DIR"

echo "Re-installing Composer with all dependencies..."
composer install

echo "Build complete! Plugin zip file: $ZIP_NAME"