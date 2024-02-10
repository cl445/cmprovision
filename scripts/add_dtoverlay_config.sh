#!/bin/bash

if [ $# -ne 1 ]; then
    echo "Error! Incorrect number of arguments."
    echo "Usage: $0 PART1"
    exit 1
fi

PART1="$1"

# Ensure the mounting point is not already mounted.
if mountpoint -q /mnt/boot; then
    echo "Error: Mount point /mnt/boot is already in use. Please unmount first."
    exit 1
fi

set -e

mkdir -p /mnt/boot
mount -t vfat "$PART1" /mnt/boot
echo "dtoverlay=dwc2,dr_mode=host" >> /mnt/boot/config.txt
umount /mnt/boot
