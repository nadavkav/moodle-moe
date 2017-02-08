@ou @ouvle @quizsbsaccess @quizsbsaccess_offlinemode
Feature: Fault-tolerant mode quizsbs setting
  In order to run quizsbszes with dodgy wifi
  As a teacher
  I need to turn the fault-tolerant quizsbs mode on and off.

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
  Scenario: Create a quizsbs with the setting on.
    When I turn editing mode on
    And I add a "quizsbs" to section "0" and I fill the form with:
      | Name                             | quizsbs with fault-tolerant mode |
      | Experimental fault-tolerant mode | Yes                           |
    And I follow "quizsbs with fault-tolerant mode"
    And I navigate to "Edit settings" node in "quizsbs administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"

  @javascript
  Scenario: Create a quizsbs with the setting off.
    When I turn editing mode on
    And I add a "quizsbs" to section "0" and I fill the form with:
      | Name                             | quizsbs without fault-tolerant mode |
      | Experimental fault-tolerant mode | No                               |
    And I follow "quizsbs without fault-tolerant mode"
    And I navigate to "Edit settings" node in "quizsbs administration"
    Then the field "Experimental fault-tolerant mode" matches value "No"

  @javascript
  Scenario: Change the setting for a quizsbs from off to on.
    Given the following "activities" exist:
      | activity   | name   | course | idnumber | offlinemode_enabled |
      | quizsbs       | quizsbs 1 | C1     | quizsbs1    | 0                   |
    When I follow "Course 1"
    And I follow "quizsbs 1"
    And I navigate to "Edit settings" node in "quizsbs administration"
    And I set the field "Experimental fault-tolerant mode" to "Yes"
    And I press "Save and display"
    And I navigate to "Edit settings" node in "quizsbs administration"
    Then the field "Experimental fault-tolerant mode" matches value "Yes"

  @javascript
  Scenario: Change the setting for a quizsbs from on to off.
    Given the following "activities" exist:
      | activity   | name   | course | idnumber | offlinemode_enabled |
      | quizsbs       | quizsbs 1 | C1     | quizsbs1    | 1                   |
    When I follow "Course 1"
    And I follow "quizsbs 1"
    And I navigate to "Edit settings" node in "quizsbs administration"
    And I set the field "Experimental fault-tolerant mode" to "No"
    And I press "Save and display"
    And I navigate to "Edit settings" node in "quizsbs administration"
    Then the field "Experimental fault-tolerant mode" matches value "No"

  @javascript
  Scenario: The experimental setting is disabled if you select an interactive behaviour.
    When I turn editing mode on
    And I add a "quizsbs" to section "0"
    And I set the field "How questions behave" to "Adaptive mode"
    Then the "Experimental fault-tolerant mode" "field" should be disabled
