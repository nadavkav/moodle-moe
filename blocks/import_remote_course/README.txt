This Moodle block type plugin is made to to ease the import process of a course backup from a remote Moodle system
(dependent on local/remote_backup_provider)

It will display a list of preset courses (with IDs)
The user (Teacher) can choose one of the courses, and a ws request will fetch the remote (alread made) backup course file
and import (merge) it with the current, empty, course.

Link to a plugin dependancy:
https://github.com/nadavkav/moodle-local_remote_backup_provider