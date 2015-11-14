AUTHOR=Author
BACK=Back
DESCRIPTION=Description
HOME=Home
INSTALL=Install
INSTALLATIONS=Installations
NAME=Name
STR_NONE="None"
OVERVIEW=Overview
PERMISSIONS=Permissions
PLUGINS=Plugins
UNINSTALL=Uninstall
UNITTEST=Unittest
UNKNOWN=Unknown
USERS=Users
VERSION=Version

ERROR_INSUFFICIENT_PERMISSIONS="You lack permissons for this action!"
ERROR_MISSING_ARGUMENTS=Missing arguments
ERROR_RESTRICTED_ACCESS=Restricted access

[ControllerSystem.Installations]
TITLE=Overview of installed extensions
UPDATE=Update?

[ControllerSystem.Installation]
TITLE=Extension details

TITLE_PRETEXT="Extension:"

DIST_URL=Distribution URL
UPDATE_URL=Update URL

UNNAMED_EXTENSION="Unnamed extension"
NO_DESC_AVAILABLE="No description available"

ERROR_NO_META="No installation meta data found!"

UPDATE_NOTIFICATION_TITLE="Hey!"
UPDATE_NOTIFICATION_BODY="An update is available! Click %update-link% to update!"

[ControllerSystem.Install]
TITLE=Install a new extension

RETURN_TO_OVERVIEW=Return to overview

PRETEXT="Choose a file from the below file browser to install. Then click the button below. You may also upload a file directly from your hard drive. Additionally I'd like to introduce a URL download at some point."
NOSCRIPT="It appears you have JavaScript disabled. JavaScript is required for this FileBrowser including the installation launcher to function! A non-JavaScript version might be introduced at some point in the future."
START_INSTALLATION=Start installation

[ControllerSystem.Uninstall]
TITLE=Uninstall extension

SUCCESS="The extension was successfully deinstalled! Click %return-link% to return to the overview."
FAILURE="The extension could not be installed! Please refer to both the given error messages and the logs."

[ControllerSystem.Update]
TITLE=Update extension

SUCCESS="The extension was successfully updated! Click %return-link% to return to the extension overview."
FAILURE="The extension could not be installed! Please refer to both the given error messages and the logs."


[ControllerTest.Overview]
PARAGRAPH1="I know unittests are quite helpful, but not until recently this project was actually
designed as a small project. Now with my goal being a hopefully widespread foundation
for developers unittests become quite critical in not only ensuring the proper
functioning of the system but also as a time saver.
Thus expect this incomplete list to be growing over the next few (i.e. all) versions."

PARAGRAPH2='Feel free to add your own unittest here. They are read from the
directory &quot;/unittest&quot;. Each test has its own subdirectory there and contains
a file called &quot;test.php&quot; which will then be included in the test view.'
