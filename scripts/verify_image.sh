#!/bin/bash

# This script verifies the image written to a device.
# It requires three arguments:
#   1. The path to the image on the storage device
#   2. The uncompressed size of the image
#   3. The sha256 hash of the uncompressed image

# Check if necessary commands are available
command -v dd >/dev/null 2>&1 || { echo >&2 "dd command not found. Aborting."; exit 1; }
command -v sha256sum >/dev/null 2>&1 || { echo >&2 "sha256sum command not found. Aborting."; exit 1; }

if [ $# -ne 3 ]; then
    echo "Error! Incorrect number of arguments."
    echo "Usage: $0 <STORAGE_PATH> <UNCOMPRESSED_SIZE> <UNCOMPRESSED_SHA256>"
    exit 1
fi

STORAGE_PATH="$1"
UNCOMPRESSED_SIZE="$2"
UNCOMPRESSED_SHA256="$3"

# Check if the image file exists and is readable
if [[ ! -r $STORAGE_PATH ]]; then
    echo "Error: File $STORAGE_PATH does not exist or is not readable."
    exit 1
fi

# Calculate the block size and count
if (($UNCOMPRESSED_SIZE < 1048576)); then
    COUNT=1
    BLOCK_SIZE=$UNCOMPRESSED_SIZE
else
    COUNT=$(($UNCOMPRESSED_SIZE / 1048576))
    BLOCK_SIZE="1M"
fi

DD_COMMAND="dd if=$STORAGE_PATH bs=$BLOCK_SIZE count=$COUNT"

set -e
sync; echo 3 > /proc/sys/vm/drop_caches
trap "echo Verification interrupted && exit 1" SIGINT SIGTERM
READ_SHA256=$($DD_COMMAND | sha256sum | awk '{print $1}')
echo "Computed SHA256: $READ_SHA256"

if [[ $READ_SHA256 = $UNCOMPRESSED_SHA256 ]]; then
    echo "Verification successful!"
else
    echo "Verification failed"
    exit 2
fi
trap - SIGINT SIGTERM
