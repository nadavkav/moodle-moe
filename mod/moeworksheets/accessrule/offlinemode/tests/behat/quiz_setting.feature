@ou @ouvle @moeworksheetsaccess @moeworksheetsaccess_offlinemode
Feature: Fault-tolerant mode moeworksheets setting
  In order to run moeworksheetszes with dodgy wifi
  As a teacher
  I need to turn the fault-tolerant moeworksheets mode on and off.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teachy    |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And I log in as "teacher"
    And I follow "Course 1"

  @javascript
  Scenario: Create a moeworksheets with the setting on.
    When I turn editing mode on
    And I add a "moeworksheets" to section "0" and I fill the form with:
      | Name                             | moeworksheets with fault-tolerant mode |
      | Experimental fault-tolerant mode | Yes                           |
    And I follow "moeworksheets with fault-tolerant mode"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"

  @javascript
  Scenario: Create a moeworksheets with the setting off.
    When I turn editing mode on
    And I add a "moeworksheets" to section "0" and I fill the form with:
      | Name                             | moeworksheets without fault-tolerant mode |
      | Experimental fault-tolerant mode | No                               |
    And I follow "moeworksheets without fault-tolerant mode"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    Then the field "Experimental fault-tolerant mode" matches value "No"

  @javascript
  Scenario: Change the setting for a moeworksheets from off to on.
    Given the following "activities" exist:
      | activity   | name   | course | idnumber | offlinemode_enabled |
      | moeworksheets       | moeworksheets 1 | C1     | moeworksheets1    | 0                   |
    When I follow "Course 1"
    And I follow "moeworksheets 1"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    And I set the field "Experimental fault-tolerant mode" to "Yes"
    And I press "Save and display"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"

  @javascript
  Scenario: Change the setting for a moeworksheets from on to off.
    Given the following "activities" exist:
      | activity   | name   | course | idnumber | offlinemode_enabled |
      | moeworksheets       | moeworksheets 1 | C1     | moeworksheets1    | 1                   |
    When I follow "Course 1"
    And I follow "moeworksheets 1"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    And I set the field "Experimental fault-tolerant mode" to "No"
    And I press "Save and display"
    And I navigate to "Edit settings" node in "moeworksheets administration"
    Then the field "Experimental fault-tolerant mode" matches value "No"

  @javascript
  Scenario: The experimental setting is disabled if you select an interactive behaviour.
    When I turn editing mode on
    And I add a "moeworksheets" to section "0"
    And I set the field "How questions behave" to "Adaptive mode"
    Then the "Experimental fault-tolerant mode" "field" should be disabled
