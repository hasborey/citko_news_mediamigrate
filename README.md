Migrate ext_news media and related files to FAL
====

Intended audience
----
Experienced TYPO3 Integrators and Developers

Status
----
Tested on a small scale, feel free to adapt to your needs.

Usage
---

**PLEASE make a backup of the database and the folder uploads/tx_news first!**

Run the migration from CLI:

`typo3/cli_dispatch.phpsh extbase mediamigrate:mediamigrate --pid PID_WITH_NEWS_RECORDS --folder fileadmin/WHATEVER`

This command copies the files from uploads/tx_news to the folder and creates FAL references for the media and related file elements of a news record.

After checking the migration you can delete the migrated files and the old references:

`typo3/cli_dispatch.phpsh extbase mediamigrate:mediadelete --pid PID_WITH_NEWS_RECORDS`

Then you will have to enter `1`, if you want to proceed.

That's it. The extension fits our purpose, feel free to modify it!

After migration delete the extension from your system.

Big thanks to my company [Citkomm](http://www.citkomm.de) for letting me publish this.


