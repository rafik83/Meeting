@event @package @product @participant
Feature: Buy participant with from the funnel
  I need to be able to buy participants

  Scenario: I can buy a participant with "Pass Jour 1"
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
    And I am logged with "user@example.net" on event "http://super-event.vimeet.proximum"
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
    And I fill in "participant_and_planning[planningQuantity][quantity]" with "1"
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
    And I should see "Formule Jumbo"
    And I should see "Pass Jour 1"
    And I should not see "Pass Jour 3"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I check the "form.payment_choice.children.paymentMode.bank_check" radio
    Then I press "package.payment.pay.label"

  Scenario: I can buy a participant "Pass Jour 3"
    Given I am logged with "user@example.net" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "package.participant.add"
    And I follow "package.participant.add"
    And I should see "Pass Jour 1"
    And I should see "package.product.notAvailable"
    And I should see "Pass Jour 3"
    Then I fill in the following:
      | email | user-2@example.net |
    And I press "sheet.participant.sendInvite"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should see "validators.participant.mustSelectProduct"
    Then I fill in the following:
      | email | user-2@example.net |
    And I select "0" from "add_participant[product]"
    # Pass Jour 3
    And I press "sheet.participant.sendInvite"
    And I should be on this page "/fr/sheet/1/package/step/1"
    Then I press "package.participant_planning.validate"
    And I should be on this page "/fr/sheet/1/package/step/2"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Pass Jour 3"
    And I should not see "Pass Jour 1"
    And I should not see "Formule Jumbo"
