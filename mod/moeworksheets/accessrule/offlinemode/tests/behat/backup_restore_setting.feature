@ou @ouvle @moeworksheetsaccess @moeworksheetsaccess_offlinemode
Feature: Fault-tolerant mode backup and restore of moeworksheets settings
  In order to reuse moeworksheetszes using fault-tolerant mode
  As a teacher
  I need be able to backup courses with and without that setting.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity   | name                | course | idnumber | offlinemode_enabled |
      | moeworksheets       | moeworksheets fault-tolerant | C1     | moeworksheets1    | 1                   |
      | moeworksheets       | moeworksheets normal         | C1     | moeworksheets2    | 0                   |
    And I log in as "admin"

  @javascript
  Scenario: Change the setting for a moeworksheets from off to on.
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I follow "moeworksheets fault-tolerant"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"
    And I follow "Course 2"
    And I follow "moeworksheets normal"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    And the field "Experimental fault-tolerant mode" matches value "No"
