#!/bin/bash

# Step 1: Identify the most recent year directory
YEAR_DIR=$(ls -d */ | grep -E '^[0-9]{4}/$' | sort -r | head -n 1 | sed 's:/$::')
if [ -z "$YEAR_DIR" ]; then
  echo "No year directories found. Please ensure directories are named with the format YYYY."
  exit 1
fi

echo "Most recent year directory: $YEAR_DIR"

# Step 2: Identify the most recent completed day
LAST_DAY=$(ls "$YEAR_DIR" | grep -E 'day-[0-9]{2}\.php' | sed -E 's/day-([0-9]{2})\.php/\1/' | sort -n | tail -n 1)
NEXT_DAY=$(printf "%02d" $((10#$LAST_DAY + 1)))

echo "Last completed day: $LAST_DAY"
echo "Next day to create: $NEXT_DAY"

# Step 3: Prompt for the day to create
read -p "Enter the day to create [$NEXT_DAY]: " CHOSEN_DAY
CHOSEN_DAY=${CHOSEN_DAY:-$NEXT_DAY} # Use default if no input is provided

# Ensure the chosen day is a valid two-digit number
if ! [[ $CHOSEN_DAY =~ ^[0-9]{1,2}$ ]]; then
  echo "Invalid day. Please enter a number between 1 and 31."
  exit 1
fi
CHOSEN_DAY=$(printf "%02d" $CHOSEN_DAY)

# Step 4: Check if the file already exists
NEW_FILE="$YEAR_DIR/day-$CHOSEN_DAY.php"
if [ -f "$NEW_FILE" ]; then
  echo "File $NEW_FILE already exists. Aborting."
  exit 1
fi

# Step 5: Copy template and rename it
TEMPLATE_FILE="template"
if [ ! -f "$TEMPLATE_FILE" ]; then
  echo "Template file '$TEMPLATE_FILE' not found. Please ensure it exists in the current directory."
  exit 1
fi

cp "$TEMPLATE_FILE" "$NEW_FILE"
echo "Copied template to: $NEW_FILE"

# Step 6: Search-replace placeholders in the new file
sed -i '' \
  -e "s/Day00/Day$CHOSEN_DAY/g" \
  -e "s/day00/day$CHOSEN_DAY/g" \
  -e "s/day-00/day-$CHOSEN_DAY/g" \
  -e "s/Day 0:/Day $CHOSEN_DAY:/g" \
  "$NEW_FILE"

echo "Replaced placeholders in: $NEW_FILE"

# Step 7: Create empty input files
DATA_DIR="$YEAR_DIR/data"
mkdir -p "$DATA_DIR"

INPUT_FILE="$DATA_DIR/day-$CHOSEN_DAY.txt"
TEST_FILE="$DATA_DIR/day-$CHOSEN_DAY-test.txt"

touch "$INPUT_FILE" "$TEST_FILE"

echo "Created empty input files:"
echo "  - $INPUT_FILE"
echo "  - $TEST_FILE"

echo "Setup complete for Day $CHOSEN_DAY!"
