#!/bin/bash

# To use this script:
# Save the code into a file, for example, upgrade.sh.
# Make the script executable: chmod +x upgrade.sh.
# Run the script: sudo ./upgrade.sh.

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root."
    exit 1
fi

# Ask user for VLSM installation path
read -p "Enter the VLSM installation path [/var/www/vlsm]: " vlsm_path
vlsm_path="${vlsm_path:-/var/www/vlsm}"

# Check if VLSM folder exists
if [ ! -d "$vlsm_path" ]; then
    echo "VLSM folder does not exist at $vlsm_path. Please first run the setup script."
    exit 1
fi

# Check for MySQL
if ! command -v mysql &>/dev/null; then
    echo "MySQL is not installed. Please first run the setup script."
    exit 1
fi

# Check for Apache
if ! command -v apache2ctl &>/dev/null; then
    echo "Apache is not installed. Please first run the setup script."
    exit 1
fi

# Check for PHP
if ! command -v php &>/dev/null; then
    echo "PHP is not installed. Please first run the setup script."
    exit 1
fi

# Check for Composer
if ! command -v composer &>/dev/null; then
    echo "Composer is not installed. Please first run the setup script."
    exit 1
fi

# Proceed with the rest of the script if all checks pass

echo "All system checks passed. Continuing with the update..."

# Update Ubuntu Packages
echo "Updating Ubuntu packages..."
apt update && apt upgrade -y
apt autoremove -y

spinner() {
    local pid=$!
    local delay=0.75
    local spinstr='|/-\'
    while [ "$(ps a | awk '{print $1}' | grep $pid)" ]; do
        local temp=${spinstr#?}
        printf " [%c]  " "$spinstr"
        local spinstr=$temp${spinstr%"$temp"}
        sleep $delay
        printf "\b\b\b\b\b\b"
    done
    printf "    \b\b\b\b"
}

# Function to list databases and get the database list
get_databases() {
    echo "Fetching available databases..."
    local IFS=$'\n'
    # Exclude the databases you do not want to back up from the list
    databases=($(mysql -u root -p"$mysql_root_password" -e "SHOW DATABASES;" | sed 1d | egrep -v 'information_schema|mysql|performance_schema|sys|phpmyadmin'))
    local -i cnt=1
    for db in "${databases[@]}"; do
        echo "$cnt) $db"
        let cnt++
    done
}

# Function to back up selected databases
backup_database() {
    local IFS=$'\n'
    # Now we use the 'databases' array from 'get_databases' function instead of querying again
    local db_list=("${databases[@]}")
    local timestamp=$(date +%Y%m%d-%H%M%S) # Adding timestamp with hours, minutes, and seconds
    for i in "$@"; do
        local db="${db_list[$i]}"
        echo "Backing up database: $db"
        mysqldump -u root -p"$mysql_root_password" "$db" | gzip >"${backup_location}/${db}_${timestamp}.sql.gz"
        if [[ $? -eq 0 ]]; then
            echo "Backup of $db completed successfully."
        else
            echo "Failed to backup database: $db"
        fi
    done
}

# Ask for MySQL root password
echo "Please enter your MySQL root password:"
read -s mysql_root_password

# Ask for the backup location and create it if it doesn't exist
read -p "Enter the backup location [/var/vlsm-backup/db/]: " backup_location
backup_location="${backup_location:-/var/vlsm-backup/db/}"

# Create the backup directory if it does not exist
if [ ! -d "$backup_location" ]; then
    echo "Backup directory does not exist. Creating it now..."
    mkdir -p "$backup_location"
    if [ $? -ne 0 ]; then
        echo "Failed to create backup directory. Please check your permissions."
        exit 1
    fi
fi

# Change to the backup directory
cd "$backup_location" || exit

# List databases and ask for user choice
get_databases
echo "Enter the numbers of the databases you want to backup, separated by space or comma, or type 'all' for all databases:"
read -r input_selections

# Convert input selection to array indexes
selected_indexes=()
if [[ "$input_selections" == "all" ]]; then
    selected_indexes=("${!databases[@]}")
else
    # Split input by space and comma
    IFS=', ' read -ra selections <<<"$input_selections"

    for selection in "${selections[@]}"; do
        if [[ "$selection" =~ ^[0-9]+$ ]]; then
            # Subtract 1 to convert from human-readable number to zero-indexed array
            selected_indexes+=($(($selection - 1)))
        else
            echo "Invalid selection: $selection. Ignoring."
        fi
    done
fi

# Backup the selected databases
backup_database "${selected_indexes[@]}"

# Ask the user if they want to backup the VLSM folder
read -p "Do you want to backup the VLSM folder before updating? (yes/no) " backup_vlsm_answer
if [[ "$backup_vlsm_answer" =~ ^[Yy][Ee][Ss]$ ]]; then
    # Backup Old VLSM Folder
    echo "Backing up old VLSM folder..."
    timestamp=$(date +%Y%m%d-%H%M%S) # Using this timestamp for consistency with database backup filenames
    backup_folder="/var/vlsm-backup/www/vlsm-backup-$timestamp"
    mkdir -p "$backup_folder"
    rsync -a --delete --exclude "public/temporary/" "$vlsm_path/" "$backup_folder/" &
    spinner # This will show the spinner until the above process is completed
else
    echo "Skipping VLSM folder backup as per user request."
fi

# Download New Version of VLSM from GitHub
echo "Downloading new version of VLSM from GitHub..."
wget -q -O vlsm-new-version.zip https://github.com/deforay/vlsm/archive/refs/heads/master.zip

# Unzip New VLSM Version
echo "Unzipping new VLSM version..."
temp_dir=$(mktemp -d)
unzip vlsm-new-version.zip -d "$temp_dir"

# Copy the unzipped content to the VLSM directory, overwriting any existing files
echo "Updating VLSM files..."
cp -RT "$temp_dir/vlsm-master/" "$vlsm_path" &
cp_pid=$!         # Save the process ID of the cp command
spinner "$cp_pid" # Start the spinner
wait $cp_pid      # Wait for the copy process to finish

# Cleanup downloaded and temporary files
rm vlsm-new-version.zip
rm -r "$temp_dir"

# Set proper permissions
chown -R www-data:www-data "$vlsm_path"

# Run Composer Update as www-data
echo "Running composer update as www-data user..."
cd "$vlsm_path"
sudo -u www-data composer update

# Run Migrations
echo "Running database migrations..."
php "$vlsm_path/app/system/migrate.php" -yq &
spinner

# Ask User to Run 'run-once' Scripts
echo "Do you want to run scripts from $vlsm_path/run-once/? (yes/no)"
read -r run_once_answer

if [[ "$run_once_answer" =~ ^[Yy][Ee][Ss]$ ]]; then
    # List the files in run-once directory
    echo "Available scripts to run:"
    files=("$vlsm_path/run-once/"*.php)
    for i in "${!files[@]}"; do
        filename=$(basename "${files[$i]}")
        echo "$((i + 1))) $filename"
    done

    # Ask which files to run
    echo "Enter the numbers of the scripts you want to run separated by commas (e.g., 1,3,6) or type 'all' to run them all."
    read -r files_to_run

    # Run selected files
    if [[ "$files_to_run" == "all" ]]; then
        for file in "${files[@]}"; do
            echo "Running $file..."
            sudo -u www-data php "$file"
        done
    else
        IFS=',' read -ra ADDR <<<"$files_to_run"
        for i in "${ADDR[@]}"; do
            # Remove any spaces in the input and correct the array index
            i=$(echo "$i" | xargs)
            file_index=$((i - 1))

            # Check if the selected index is within the range of available files
            if [[ $file_index -ge 0 ]] && [[ $file_index -lt ${#files[@]} ]]; then
                file="${files[$file_index]}"
                echo "Running $file..."
                sudo -u www-data php "$file"
            else
                echo "Invalid selection: $i. Please select a number between 1 and ${#files[@]}. Skipping."
            fi
        done
    fi
fi

# Run the PHP script for remote data sync
echo "Running remote data sync script. Please wait..."
php "$vlsm_path/app/scheduled-jobs/remote/commonDataSync.php" &

# Get the PID of the commonDataSync.php script
pid=$!

# Show a simple progress indicator
while kill -0 $pid 2>/dev/null; do
    echo -n "."
    sleep 1
done

echo "Remote data sync completed."

service apache2 restart

echo "VLSM update complete."
