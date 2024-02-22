#!/bin/bash

if [ $# -ne 2 ]; then
    echo "Error! Incorrect number of arguments."
    echo "Usage: $0 STORAGE PART2"
    exit 1
fi

STORAGE="$1"
PART2="$2"

# Check if necessary commands are available
command -v parted >/dev/null 2>&1 || { echo >&2 "parted command not found. Aborting."; exit 1; }
command -v resize2fs >/dev/null 2>&1 || { echo >&2 "resize2fs command not found. Aborting."; exit 1; }

set -e

parted -s $STORAGE resizepart 2 -1 quit
resize2fs -f "$PART2"

mkdir -p /mnt/boot /mnt/root
mount -t ext4 $PART2 /mnt/root
umount /mnt/root
mount -t vfat $PART1 /mnt/boot
sed -i 's| init=/usr/lib/raspi-config/init_resize\.sh||' /mnt/boot/cmdline.txt
umount /mnt/boot
