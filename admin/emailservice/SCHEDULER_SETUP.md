# Email Campaign Scheduler Setup

The scheduled sending feature requires a cron job to automatically send campaigns at their scheduled times.

## Cron Job Setup

Add this line to your crontab to check for scheduled campaigns every minute:

```bash
# Edit crontab
crontab -e

# Add this line (replace /path/to/your/project with actual path):
* * * * * /usr/bin/php /path/to/your/project/emailservice/scheduler.php >> /path/to/your/project/emailservice/scheduler.log 2>&1
```

## Example for your current path:
```bash
* * * * * /usr/bin/php /Users/dustintaylor/Documents/programming/mywebsites/016\ framersmethod3/framersmethod/emailservice/scheduler.php >> /Users/dustintaylor/Documents/programming/mywebsites/016\ framersmethod3/framersmethod/emailservice/scheduler.log 2>&1
```

## What it does:
- Runs every minute (`* * * * *`)
- Executes `scheduler.php` using PHP
- Logs output to `scheduler.log`
- Redirects errors to the log file (`2>&1`)

## Logs
Check `scheduler.log` in the emailservice directory to see:
- When the scheduler runs
- Which campaigns were processed
- Any errors that occurred

## Alternative (for testing)
You can manually run the scheduler:
```bash
cd /path/to/your/project/emailservice
php scheduler.php
```

## Notes:
- The scheduler converts user timezones to UTC for accurate scheduling
- Campaigns are processed in chronological order
- Failed campaigns are marked as 'failed' status
- The scheduler logs all activity for monitoring