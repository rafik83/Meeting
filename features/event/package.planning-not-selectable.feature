@event @package
Feature: Planning is not selectable

  Scenario: I can not select planning
    Given the database is purged
    And the event "ForumPHP" is created
    And there is a type in this event
    And there is a package "Package adhérent" for this event
    And this package is assigned to this type
    And there is a plan called "Formule adhérent" with a price of "567"
    And this plan is assigned to this package
    And there is a product Participant called "Pass 2 jours" with a price of "150" and a max quantity of 1
    And this product participant is assigned to this package
    And there is a planning called "Planning RDV" with a price of "999"
    And this product planning is assigned to this package
    And in this package, planning is not selectable
    And there is a sheet for this type with the title "Proximum Group"
    And the user "user@example.net" is created
    And there is a participant for this sheet and this user
    And I am logged with "user@example.net" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "Formule adhérent"
    And I select "0" from "plans[plan]"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Pass 2 jours"
    When I select "Pass 2 jours" from "participant_and_planning_1"
    And I should not see "Planning RDV"
    Then I press "package.participant_planning.validate"
    And I should be on this page "/fr/sheet/1/package/step/3"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    And I check the "gender.man" radio
    And I fill in the following:
      | form.billing_info_update.children.lastname.label  | Jean         |
      | form.billing_info_update.children.firstname.label | Test         |
      | form.billing_info_update.children.function.label  | DG           |
      | form.billing_info_update.children.phone.label     | +33456789    |
      | form.billing_info_update.children.mobile.label    | +33456789    |
      | form.billing_info_update.children.email.label     | jean@test.fr |
      | form.billing_info_update.children.company.label   | ELAO-TEST    |
      | form.billing_info_update.children.street.label    | 10 Rue test  |
      | form.billing_info_update.children.zipcode.label   | 75002        |
      | form.billing_info_update.children.city.label      | PARIS        |
      | form.billing_info_update.children.country.label   | FR           |
      | form.billing_info_update.children.vatNumber.label | 123456789    |
    And I press "form.billing_info_update.children.submit.label"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Formule adhérent"
    And I should see "Pass 2 jours"
    And I should not see "Planning RDV"
