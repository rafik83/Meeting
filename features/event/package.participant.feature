@event @package @product @participant
Feature: Buy participant with from the funnel
  I need to be able to buy participants

  Scenario: I can buy a participant from the sheet
    Given the database is purged
    And the event "Google Christmas Party" is created
    And there is a type in this event
    And there is a package "Package for participant" for this event
    And this package is assigned to this type
    And there is a plan called "Formule Jumbo" with a price of "567"
    And this plan is assigned to this package
    And there is a product Participant called "Pass Jour 1" with a price of "123" and a max quantity of 1
    And this product participant is assigned to this package
    And this plan includes this product participant 1 times
    And there is a product Participant called "Pass Jour 3" with a price of "321" and a max quantity of 1
    And this product participant is assigned to this package
    And there is a planning called "Planning RDV" with a price of "234"
    And this product planning is assigned to this package
    And there is a plan called "Formule Little" with a price of "300"
    And this plan is assigned to this package
    And there is a sheet for this type with the title "Proximum Group"
    And the user "user@example.net" is created
    And there is a participant for this sheet and this user
    When I am logged with "user@example.net" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "Formule Jumbo"
    And I select "0" from "plans[plan]"
    #Formule Jumbo
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Pass Jour 1"
    And I should see "Pass Jour 3"
    When I select "Pass Jour 1" from "participant_and_planning_1"
    And I fill in "participant_and_planning[planningQuantity]" with "1"
    Then I press "package.participant_planning.validate"
