@ou @ouvle @moeworksheetsaccess @moeworksheetsaccess_offlinemode
Feature: Fault-tolerant mode navigation without page reloads
  In order to attempt moeworksheetszes with dodgy wifi
  As a student
  I need to be able to navigate between pages of the moeworksheets without a page reload.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | student  | Study     |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | student | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name       | questiontext    |
      | Test questions   | truefalse | Question A | Answer me A |
      | Test questions   | truefalse | Question B | Answer me B |
      | Test questions   | truefalse | Question C | Answer me C |
    And the following "activities" exist:
      | activity   | name                | course | idnumber | questionsperpage | offlinemode_enabled |
      | moeworksheets       | moeworksheets fault-tolerant | C1     | moeworksheets1    | 1                | 1                   |
    And moeworksheets "moeworksheets fault-tolerant" contains the following questions:
      | Question A | 1 |
      | Question B | 2 |
      | Question C | 3 |
    And I log in as "student"
    And I follow "Course 1"
    And I follow "moeworksheets fault-tolerant"

  @javascript
  Scenario: Start a moeworksheets attempt, and verify we see only page 1.
    When I press "Attempt moeworksheets now"
    Then I should see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#moeworksheetsnavbutton1.thispage" "css_element" should exist
    And "#moeworksheetsnavbutton2" "css_element" should exist
    And "#moeworksheetsnavbutton2.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton3" "css_element" should exist
    And "#moeworksheetsnavbutton3.thispage" "css_element" should not exist

  @javascript
  Scenario: Start a moeworksheets attempt and verify that switching to page 2 works.
    When I press "Attempt moeworksheets now"
    And I start watching to see if a new page loads
    And I click on "Question 2" "link" in the "moeworksheets navigation" "block"
    Then I should not see "Answer me A"
    And I should see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#moeworksheetsnavbutton1" "css_element" should exist
    And "#moeworksheetsnavbutton1.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton2.thispage" "css_element" should exist
    And "#moeworksheetsnavbutton3" "css_element" should exist
    And "#moeworksheetsnavbutton3.thispage" "css_element" should not exist
    And a new page should not have loaded since I started watching
    # Now successfully navigate away, or the following test will fail.
    And I click on "Miscellaneous" "link" confirming the dialogue

  @javascript
  Scenario: Start a moeworksheets attempt and verify that switching to the summary works.
    When I press "Attempt moeworksheets now"
    And I start watching to see if a new page loads
    And I click on "Finish attempt ..." "link" in the "moeworksheets navigation" "block"
    Then I should not see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should see "Summary of attempt"
    And "#moeworksheetsnavbutton1" "css_element" should exist
    And "#moeworksheetsnavbutton1.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton2" "css_element" should exist
    And "#moeworksheetsnavbutton2.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton3" "css_element" should exist
    And "#moeworksheetsnavbutton3.thispage" "css_element" should not exist
    And a new page should not have loaded since I started watching

  @javascript
  Scenario: Start a moeworksheets attempt and verify that switching from the summary works.
    When I press "Attempt moeworksheets now"
    And I start watching to see if a new page loads
    And I click on "Finish attempt ..." "link" in the "moeworksheets navigation" "block"
    And I click on "3" "link" in the "moeworksheetssummaryofattempt" "table"
    Then I should not see "Answer me A"
    And I should not see "Answer me B"
    And I should see "Answer me C"
    And I should not see "Summary of attempt"
    And "#moeworksheetsnavbutton1" "css_element" should exist
    And "#moeworksheetsnavbutton1.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton2" "css_element" should exist
    And "#moeworksheetsnavbutton2.thispage" "css_element" should not exist
    And "#moeworksheetsnavbutton3.thispage" "css_element" should exist
    And a new page should not have loaded since I started watching
    # Now successfully navigate away, or the following test will fail.
    And I click on "Miscellaneous" "link" confirming the dialogue
