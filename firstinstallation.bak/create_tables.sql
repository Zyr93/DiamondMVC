DROP TABLE IF EXISTS `USERS`, `USER_GROUPS`, `GROUPS`, `SYS_CACHE`, `SYS_PERMS`, `SYS_GROUP_PERMS`, `SYS_USER_PERMS`, `SYS_PLUGIN_META`;

CREATE TABLE `USERS` (
	`UID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`USERNAME` VARCHAR(20),
	`EMAIL` VARCHAR(100),
	`PASSWORD` VARCHAR(64),
	`DELETED` BOOL,
	PRIMARY KEY(`UID`)
) COLLATE utf8_general_ci;


CREATE TABLE `USER_GROUPS` (
	`UID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`USERID` INTEGER UNSIGNED NOT NULL,
	`GROUPID` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY(`UID`)
) COLLATE utf8_general_ci;


CREATE TABLE `GROUPS` (
	`UID` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`NAME` VARCHAR(100),
	`DESCRIPTION` TEXT,
	PRIMARY KEY(`UID`)
) COLLATE utf8_general_ci;


CREATE TABLE `SYS_CACHE` (
	`UID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`NAME` VARCHAR(100),
	`VALUE` TEXT,
	`EXPIRES` INT UNSIGNED NOT NULL,
	PRIMARY KEY(`UID`)
) COLLATE utf8_general_ci;


CREATE TABLE `SYS_PERMS` (
	`UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`NAME` VARCHAR(100),
	`DISPLAY` VARCHAR(100),
	`DESCRIPTION` TEXT,
	`TYPE` SMALLINT,
	PRIMARY KEY(`UID`)
) COLLATE utf8_general_ci;


CREATE TABLE `SYS_GROUP_PERMS` (
	`GROUPID` INT UNSIGNED NOT NULL,
	`PERMID` INT UNSIGNED NOT NULL,
	`LEVEL` SMALLINT,
	PRIMARY KEY(`GROUPID`, `PERMID`)
) COLLATE utf8_general_ci;


CREATE TABLE `SYS_USER_PERMS` (
	`USERID` INT UNSIGNED NOT NULL,
	`PERMID` INT UNSIGNED NOT NULL,
	`LEVEL` SMALLINT,
	PRIMARY KEY(`USERID`, `PERMID`)
) COLLATE utf8_general_ci;


CREATE TABLE `SYS_PLUGIN_META` (
	`NAME` VARCHAR(100),
	`PRIORITY` SMALLINT NOT NULL,
	PRIMARY KEY(`NAME`)
) COLLATE utf8_general_ci;


INSERT INTO `SYS_PERMS` ( `NAME`, `DISPLAY`, `DESCRIPTION`, `TYPE` ) VALUES
	('sys_admin',                            'System: Admin',                                    'User is an administrator and has any permission imaginable.', 0),
	('sys_access',                           'System: Access',                                   'User is permitted to access the system administration.', 0),
	('sys_users_view',                       'System: View users',                               'User may view a list of all registered users.', 0),
	('sys_user_add',                         'System: Add user',                                 'User can create a new user.', 0),
	('sys_user_remove',                      'System: Remove user',                              'User may remove users.', 1),
	('sys_user_remove_required_level',       'System: Remove user (required level)',             'Required permission level for users attempting to remove the associated user.', 1),
	('sys_user_alter_basic',                 'System: Change user data',                         'User may change a user\'s basic data.', 1),
	('sys_user_alter_basic_required_level',  'System: Change user data (required level)',        'Required permission level for users attempting to change the associated user\'s basic data.', 1),
	('sys_user_group_add',                   'System: Add user to group',                        'User is allowed to add another user to a user group.', 1),
	('sys_user_group_add_required_level',    'System: Add user to group (required level)',       'Required permission level for users attempting to add the associated user to a new group.', 1),
	('sys_user_group_remove',                'System: Remove user from group',                   'User is allowed to remove another user from a user group.', 1),
	('sys_user_group_remove_required_level', 'System: Remove user from group (required level)',  'Required permission level for users attempting to remove the associated user from one of their groups. Prevents e.g. the support staff from removing an administrator from the admin team.', 1),
	('sys_user_perms_change',                'System: Change user permissions',                  'User is enabled to change the permissions of another user.', 1),
	('sys_user_perms_change_required_level', 'System: Change user permissions (required level)', 'Required permission level for users attempting to change this user\'s permissions. Prevents e.g. the support staff from revoking admin privileges.', 1),
	('sys_user_reset_password',              'System: Reset user password',                      'User is permitted to reset any user\'s password. Only the respective user is notified about this reset and user may set the password afterwards.', 0),
	('sys_perms_view',                       'System: View permissions',                         'User may view a list of all registered permissions.', 0),
	('sys_perms_add',                        'System: Add permission',                           'User is enabled to add new permissions to the database.', 0),
	('sys_perms_remove',                     'System: Remove permission',                        'User is enabled to remove existing permissions from the database. Beware not to remove system permissions or permissions added by extensions! Grant permission with care.', 0),
	('sys_perms_alter',                      'System: Alter permission',                         'User can change a permission\'s data such as name, display text, description, and type.', 0);

INSERT INTO `GROUPS` (`NAME`, `DESCRIPTION`) VALUES
('Administrators', 'Administrators of the website. Usually only a handful of individuals are in this group as it, by default, has the most dangerous permissions.

Moderators have similar permissions but are unable to alter any of the permissions - which makes them a much safer administrative group.'),
('Moderators', 'Moderators are administrative forces of your website lacking the ability to alter permissions in general or permissions of users in the Administrators group.');

INSERT INTO `SYS_GROUP_PERMS` (`GROUPID`, `PERMID`, `LEVEL`) VALUES (1, 1, 1);
INSERT INTO `USER_GROUPS` (`USERID`, `GROUPID`) VALUES (1, 1);
