# osTicket archival and cleanup utility
A simple utility to archive all closed tickets beyond a certain age to disk (including attachments) and delete them from the database. The ticket messages are written to disk as JSON files. Do note that the archival data cannot be restored to the database. The utility has only been tested with osTicket version 1.8.

## Usage
Copy the `archive` directory to the osTicket installation directory. Edit the settings in `config.php`. Make sure the `tickets` and `attachments` directories are given proper write permissions (666).

Execute the script by running `php archive.php` from the terminal.

**Important:** The archival process is a destructive operation. The closed tickets and their attachments archived to the disk are irrevocably *deleted* from the database. Since the archived copy is a simple JSON dump file with just the necessary information, it is not possible to restore the archived data back to the database. Only use this utility if you are trying to reduce your database size by permanently archiving old tickets. Take a look at `sample.json` to see the information archived from a sample ticket.

Attachments are stored in the `attachments` directory with filenames in the following format. `ticket_id`_`thread_id`.`extension`

### Disclaimer
This program is distributed in the hope that it will be useful, but is provided AS IS with ABSOLUTELY NO WARRANTY; The entire risk as to the quality and performance of the program is with you. The author provides no guarantees or warranties and is not liable for the outcome stemming from the use of the program.