#!/usr/bin/env bash
set -euo pipefail
PLUGSLUG="bahai-enroll-form-divi"
OUTDIR="build"
WORKDIR="$OUTDIR/$PLUGSLUG"
rm -rf "$OUTDIR"
mkdir -p "$WORKDIR"
rsync -a   --exclude ".git"   --exclude "vendor"   --exclude "node_modules"   --exclude ".github"   --exclude "build"   --exclude "tests"   --exclude ".DS_Store"   --exclude "*.zip"   ./ "$WORKDIR/"
cd "$OUTDIR"
zip -rq "${PLUGSLUG}.zip" "$PLUGSLUG"
echo "Built ${PLUGSLUG}.zip in ${OUTDIR}/"
