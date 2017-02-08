@ou @ouvle @quizsbsaccess @quizsbsaccess_offlinemode
Feature: Fault-tolerant mode navigation without page reloads for a quizsbs in sequential mode.
  In order to attempt quizsbszes with dodgy wifi
  As a student
  I need to be able to navigate between pages of the quizsbs even if the quizsbs uses sequential navigation.

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
      | activity   | name                | course | idnumber | offlinemode_enabled | navmethod  |
      | quizsbs       | quizsbs fault-tolerant | C1     | quizsbs1    | 1                   | sequential |
    And quizsbs "quizsbs fault-tolerant" contains the following questions:
      | Question A | 1 |
      | Question B | 2 |
      | Question C | 3 |
    And I log in as "student"
    And I follow "Course 1"
    And I follow "quizsbs fault-tolerant"

  @javascript
  Scenario: Start a quizsbs attempt, and verify we see only page 1.
    When I press "Attempt quizsbs now"
    Then I should see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#quizsbsnavbutton1.thispage" "css_element" should exist
    And "#quizsbsnavbutton2" "css_element" should exist
    And "#quizsbsnavbutton2.thispage" "css_element" should not exist
    And "#quizsbsnavbutton3" "css_element" should exist
    And "#quizsbsnavbutton3.thispage" "css_element" should not exist

  @javascript
  Scenario: Clicking on a nav button has no effect.
    When I press "Attempt quizsbs now"
    And I click on "#quizsbsnavbutton2" "css_element"
    Then I should see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#quizsbsnavbutton1.thispage" "css_element" should exist
    And "#quizsbsnavbutton2" "css_element" should exist
    And "#quizsbsnavbutton2.thispage" "css_element" should not exist
    And "#quizsbsnavbutton3" "css_element" should exist
    And "#quizsbsnavbutton3.thispage" "css_element" should not exist

  @javascript
  Scenario: Start a quizsbs attempt and verify that next works.
    When I press "Attempt quizsbs now"
    And I start watching to see if a new page loads
    And I press "Next"
    Then I should not see "Answer me A"
    And I should see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#quizsbsnavbutton1" "css_element" should exist
    And "#quizsbsnavbutton1.thispage" "css_element" should not exist
    And "#quizsbsnavbutton2.thispage" "css_element" should exist
    And "#quizsbsnavbutton3" "css_element" should exist
    And "#quizsbsnavbutton3.thispage" "css_element" should not exist
    And a new page should not have loaded since I started watching
    # Now successfully navigate away, or the following test will fail.
    And I click on "Miscellaneous" "link" confirming the dialogue

  @javascript
  Scenario: Start a quizsbs attempt and verify that switching to the summary works.
    When I press "Attempt quizsbs now"
    And I start watching to see if a new page loads
    And I click on "Finish attempt ..." "link" in the "quizsbs navigation" "block"
    Then I should not see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should see "Summary of attempt"
    And "#quizsbsnavbutton1" "css_element" should exist
    And "#quizsbsnavbutton1.thispage" "css_element" should not exist
    And "#quizsbsnavbutton2" "css_element" should exist
    And "#quizsbsnavbutton2.thispage" "css_element" should not exist
    And "#quizsbsnavbutton3" "css_element" should exist
    And "#quizsbsnavbutton3.thispage" "css_element" should not exist
    And a new page should not have loaded since I started watching

  @javascript
  Scenario: Start a quizsbs attempt and verify that switching back from the summary works.
    When I press "Attempt quizsbs now"
    And I start watching to see if a new page loads
    And I click on "Finish attempt ..." "link" in the "quizsbs navigation" "block"
    And I press "Return to attempt"
    Then I should see "Answer me A"
    And I should not see "Answer me B"
    And I should not see "Answer me C"
    And I should not see "Summary of attempt"
    And "#quizsbsnavbutton1.thispage" "css_element" should exist
    And "#quizsbsnavbutton2" "css_element" should exist
    And "#quizsbsnavbutton2.thispage" "css_element" should not exist
    And "#quizsbsnavbutton3" "css_element" should exist
    And "#quizsbsnavbutton3.thispage" "css_element" should not exist
    And a new page should not have loaded since I started watching
