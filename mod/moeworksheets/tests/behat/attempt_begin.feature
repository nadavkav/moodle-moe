@mod @mod_moeworksheets
Feature: The various checks that may happen when an attept is started
  As a student
  In order to start a moeworksheets with confidence
  I need to be waned if there is a time limit, or various similar things

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student  | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext               |
      | Test questions   | truefalse   | TF1   | Text of the first question |

  @javascript
  Scenario: Start a moeworksheets with no time limit
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber |
      | moeworksheets       | moeworksheets 1 | moeworksheets 1 description | C1     | moeworksheets1    |
    And moeworksheets "moeworksheets 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    When I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets 1"
    And I press "Attempt moeworksheets now"
    Then I should see "Text of the first question"

  @javascript
  Scenario: Start a moeworksheets with time limit and password
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timelimit | moeworksheetspassword |
      | moeworksheets       | moeworksheets 1 | moeworksheets 1 description | C1     | moeworksheets1    | 3600      | Frog         |
    And moeworksheets "moeworksheets 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    When I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets 1"
    And I press "Attempt moeworksheets now"
    Then I should see "To attempt this moeworksheets you need to know the moeworksheets password" in the "Start attempt" "dialogue"
    And I should see "The moeworksheets has a time limit of 1 hour. Time will " in the "Start attempt" "dialogue"
    And I set the field "moeworksheets password" to "Frog"
    And I click on "Start attempt" "button" in the "Start attempt" "dialogue"
    And I should see "Text of the first question"

  @javascript
  Scenario: Cancel starting a moeworksheets with time limit and password
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timelimit | moeworksheetspassword |
      | moeworksheets       | moeworksheets 1 | moeworksheets 1 description | C1     | moeworksheets1    | 3600      | Frog         |
    And moeworksheets "moeworksheets 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    When I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets 1"
    And I press "Attempt moeworksheets now"
    And I click on "Cancel" "button" in the "Start attempt" "dialogue"
    Then I should see "moeworksheets 1 description"
    And "Attempt moeworksheets now" "button" should be visible

  @javascript
  Scenario: Start a moeworksheets with time limit and password, get the password wrong first time
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timelimit | moeworksheetspassword |
      | moeworksheets       | moeworksheets 1 | moeworksheets 1 description | C1     | moeworksheets1    | 3600      | Frog         |
    And moeworksheets "moeworksheets 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    When I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets 1"
    And I press "Attempt moeworksheets now"
    And I set the field "moeworksheets password" to "Toad"
    And I click on "Start attempt" "button" in the "Start attempt" "dialogue"
    Then I should see "moeworksheets 1 description"
    And I should see "To attempt this moeworksheets you need to know the moeworksheets password"
    And I should see "The moeworksheets has a time limit of 1 hour. Time will "
    And I should see "The password entered was incorrect"
    And I set the field "moeworksheets password" to "Frog"
    # On Mac/FF tab key is needed as text field in dialogue and page have same id.
    And I press tab key in "moeworksheets password" "field"
    And I press "Start attempt"
    And I should see "Text of the first question"

  @javascript
  Scenario: Start a moeworksheets with time limit and password, get the password wrong first time then cancel
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timelimit | moeworksheetspassword |
      | moeworksheets       | moeworksheets 1 | moeworksheets 1 description | C1     | moeworksheets1    | 3600      | Frog         |
    And moeworksheets "moeworksheets 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    When I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets 1"
    And I press "Attempt moeworksheets now"
    And I set the field "moeworksheets password" to "Toad"
    And I click on "Start attempt" "button" in the "Start attempt" "dialogue"
    And I should see "moeworksheets 1 description"
    And I should see "To attempt this moeworksheets you need to know the moeworksheets password"
    And I should see "The moeworksheets has a time limit of 1 hour. Time will "
    And I should see "The password entered was incorrect"
    And I set the field "moeworksheets password" to "Frog"
    # On Mac/FF tab key is needed as text field in dialogue and page have same id.
    And I press tab key in "moeworksheets password" "field"
    And I press "Cancel"
    Then I should see "moeworksheets 1 description"
    And "Attempt moeworksheets now" "button" should be visible
