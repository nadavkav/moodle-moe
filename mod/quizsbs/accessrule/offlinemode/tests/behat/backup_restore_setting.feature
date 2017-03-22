@ou @ouvle @quizsbsaccess @quizsbsaccess_offlinemode
Feature: Fault-tolerant mode backup and restore of quizsbs settings
  In order to reuse quizsbszes using fault-tolerant mode
  As a teacher
  I need be able to backup courses with and without that setting.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity   | name                | course | idnumber | offlinemode_enabled |
      | quizsbs       | quizsbs fault-tolerant | C1     | quizsbs1    | 1                   |
      | quizsbs       | quizsbs normal         | C1     | quizsbs2    | 0                   |
    And I log in as "admin"

  @javascript
  Scenario: Change the setting for a quizsbs from off to on.
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I follow "quizsbs fault-tolerant"
    And I navigate to "Edit settings" node in "quizsbs administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"
    And I follow "Course 2"
    And I follow "quizsbs normal"
    And I navigate to "Edit settings" node in "quizsbs administration"
    And the field "Experimental fault-tolerant mode" matches value "No"
