#!/bin/bash

# Step 1: Prompt for the year, default to the current year
CURRENT_YEAR=$(date +"%Y")
read -p "Enter the year [$CURRENT_YEAR]: " CHOSEN_YEAR
CHOSEN_YEAR=${CHOSEN_YEAR:-$CURRENT_YEAR} # Use current year if no input is provided

# Validate the year input
if ! [[ $CHOSEN_YEAR =~ ^[0-9]{4}$ ]]; then
  echo "Invalid year. Please enter a valid 4-digit year."
  exit 1
fi

# Step 2: Ensure the year directory exists, create it if necessary
YEAR_DIR="$CHOSEN_YEAR"
if [ ! -d "$YEAR_DIR" ]; then
  echo "Year directory '$YEAR_DIR' does not exist. Creating it now."
  mkdir -p "$YEAR_DIR"
fi

echo "Selected year directory: $YEAR_DIR"

# Step 3: Identify the most recent completed day
if [ -z "$(ls "$YEAR_DIR" | grep -E 'day-[0-9]{2}\.php')" ]; then
  LAST_DAY=0
else
  LAST_DAY=$(ls "$YEAR_DIR" | grep -E 'day-[0-9]{2}\.php' | sed -E 's/day-([0-9]{2})\.php/\1/' | sort -n | tail -n 1)
  LAST_DAY=$((10#$LAST_DAY)) # Convert to a proper base-10 number
fi
NEXT_DAY=$((LAST_DAY + 1))
NEXT_DAY=$(printf "%02d" $NEXT_DAY)

if [ "$LAST_DAY" -eq 0 ]; then
  echo "No completed days yet."
else
  echo "Last completed day: $LAST_DAY"
fi
echo "Next day to create: $NEXT_DAY"

# Step 4: Prompt for the day to create
read -p "Enter the day to create [$NEXT_DAY]: " CHOSEN_DAY
CHOSEN_DAY=${CHOSEN_DAY:-$NEXT_DAY} # Use default if no input is provided

# Ensure the chosen day is a valid number and remove leading zeros
if ! [[ $CHOSEN_DAY =~ ^[0-9]{1,2}$ ]]; then
  echo "Invalid day. Please enter a number between 1 and 31."
  exit 1
fi
CHOSEN_DAY=$(printf "%02d" $((10#$CHOSEN_DAY)))

# Step 5: Check if the file already exists
NEW_FILE="$YEAR_DIR/day-$CHOSEN_DAY.php"
if [ -f "$NEW_FILE" ]; then
  echo "File $NEW_FILE already exists. Aborting."
  exit 1
fi

# Step 6: Copy template and rename it
TEMPLATE_FILE="template"
if [ ! -f "$TEMPLATE_FILE" ]; then
  echo "Template file '$TEMPLATE_FILE' not found. Please ensure it exists in the current directory."
  exit 1
fi

cp "$TEMPLATE_FILE" "$NEW_FILE"
echo "Copied template to: $NEW_FILE"

# Step 7: Search-replace placeholders in the new file
sed -i '' \
  -e "s/Day00/Day$CHOSEN_DAY/g" \
  -e "s/day00/day$CHOSEN_DAY/g" \
  -e "s/day-00/day-$CHOSEN_DAY/g" \
  -e "s/Day 0:/Day $CHOSEN_DAY:/g" \
  -e "s/0000/$CHOSEN_YEAR/g" \
  "$NEW_FILE"

echo "Replaced placeholders in: $NEW_FILE"

# Step 8: Create empty input files
DATA_DIR="$YEAR_DIR/data"
mkdir -p "$DATA_DIR"

INPUT_FILE="$DATA_DIR/day-$CHOSEN_DAY.txt"
TEST_FILE="$DATA_DIR/day-$CHOSEN_DAY-test.txt"

touch "$INPUT_FILE" "$TEST_FILE"

echo "Created empty input files:"
echo "  - $INPUT_FILE"
echo "  - $TEST_FILE"

echo "Setup complete for Day $CHOSEN_DAY in Year $CHOSEN_YEAR!"
