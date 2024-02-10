#!/bin/bash

# This script flashes the EEPROM firmware on a device using a specified SPI interface.
# It requires three arguments:
#   1. The URL of the EEPROM firmware file
#   2. The expected SHA256 hash of the firmware file
#   3. The SPI interface device path (e.g., /dev/spidev1.0)

# Check if the correct number of arguments was provided
if [ "$#" -ne 3 ]; then
    echo "Error: Incorrect number of arguments."
    echo "Usage: $0 <EEPROM_FIRMWARE_URL> <EXPECTED_SHA256> <SPI_INTERFACE>"
    exit 1
fi

EEPROM_URL="$1"
EXPECTED_SHA256="$2"
SPI_INTERFACE="$3"

# Temporary file to store the downloaded firmware
TEMP_FIRMWARE_PATH=$(mktemp)

echo "Downloading EEPROM firmware from: $EEPROM_URL"

# Download the firmware file
if ! curl --retry 10 --silent --show-error -o "$TEMP_FIRMWARE_PATH" "$EEPROM_URL"; then
    echo "Error: Failed to download the firmware file."
    rm -f "$TEMP_FIRMWARE_PATH"
    exit 1
fi

echo "Download complete. Verifying SHA256 hash..."

# Compute the SHA256 hash of the downloaded file
ACTUAL_SHA256=$(sha256sum "$TEMP_FIRMWARE_PATH" | awk '{print $1}')

# Verify the SHA256 hash
if [ "$EXPECTED_SHA256" != "$ACTUAL_SHA256" ]; then
    echo "Error: SHA256 hash does not match. Expected: $EXPECTED_SHA256, got: $ACTUAL_SHA256."
    rm -f "$TEMP_FIRMWARE_PATH"
    exit 1
fi

echo "SHA256 hash verified. Flashing the firmware using SPI interface $SPI_INTERFACE..."

# Flash the firmware
if ! flashrom -p "linux_spi:dev=$SPI_INTERFACE,spispeed=16000" -w "$TEMP_FIRMWARE_PATH"; then
    echo "Error: Failed to flash the EEPROM firmware."
    rm -f "$TEMP_FIRMWARE_PATH"
    exit 1
fi

echo "Firmware flashing successful."

# Clean up
rm -f "$TEMP_FIRMWARE_PATH"
