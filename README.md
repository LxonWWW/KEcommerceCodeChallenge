# KEcommerceCodeChallenge

# Usage

### Run the script
```
cd 'KEcommerceCodeChallenge'

Example 1: php run.php 'YOUR_FILE.xml'

Example 2: php ./run.php 'YOUR_FILE.xml' 'updateallowed'

---

eg. php ./run.php './feed.xml' 'updateallowed'
```

### Parameters
```
Parameter 1.: This project's PHP file.
Parameter 2.: The XML file to process.
Parameter 3.: If data in the database, that already exists should be updated, if something has changed. (Optional: Can be empty)
```

**PROTIP: You can use the portable php excutable in "`\external\php\ext`" if you're on windows, so no setup is needed :).**

# Specifing the target database
You can find a config.json in the projects root directory. Just change the data there to change the target database.
Please refer to the [manual here](https://www.php.net/manual/en/pdo.installation.php) to configure your server accordingly and to see the available database drivers.
 
# Summary

This program imports data from an xml file and imports it to the specified database.
By default this is a sqlite database, located in the php directory in "`./external/php/ext/`", for better testing on other devices (requires less setup).
The user can choose if data should be updated, if it has changed by the 'updateallowed' parameter (See Parameters 3.).
Errors are logged to the error.log file in the root directory.

**It was chosen to use vanilla PHP (with default libraries) because it's sufficient enough for this task, doesn't need a lot to set-up before using and is more portable. In an productive environment with for example symfony I would rather still use symfony of course, if thats what we rely on.**

# Given Task: Coding Task – Data Feed 
### (Junior) PHP Engineer (all genders)
### Goal 
We would like to see a command-line program that should process a local XML file (feed.xml) and push the data of that XML file to a DB of your choice (e.g., SQLite) 

You are free to use any library or framework you need or feel comfortable with. You have a free choice of tools. **Please use PHP!**
### Specifications 
The program should be easily extendable, e.g., we could use different data storage to read data from or to push data to. This should be configurable. 
Errors should be written to a logfile
The application should be tested.
