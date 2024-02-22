#!/bin/bash

# The mmc command is not a standard command and might not be present on all systems.
if ! command -v mmc &>/dev/null; then
    echo "The mmc command was not found. This script requires the mmc command. Aborting."
    exit 1
fi

set +e

MAXSIZEKB=$(mmc extcsd read /dev/mmcblk0 | grep MAX_ENH_SIZE_MULT -A 1 | grep -o '[0-9]\+ ')
mmc enh_area set -ei 0 $MAXSIZEKB /dev/mmcblk0
if [ $? -eq 0 ]; then
    echo "MMC enhance area set successfully. Rebooting now..."
    sleep 2
    reboot -f
else
    echo "MMC enhance area set failed. Please try again."
    exit 1
fi
