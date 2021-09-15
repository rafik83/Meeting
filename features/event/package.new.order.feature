@event
@package
@order
@product
Feature: Edit my package
  I need to be able to edit my package after a first order

  Scenario: I can edit my package
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"

    And there is a type "Exposant" in this event

    # package with product of type "Participant"
    And there is a package "Pack participants+rdv" for this event
    And there is a product Participant called "Participant Supplémentaire" with a price of "130" and a max quantity of 1
    And this product participant is assigned to this package
    And there is a product Participant called "Participant inclus" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And this package is assigned to this type
    And there is a plan called "Formule premium" with a price of "99"
    And this plan is assigned to this package
    And this plan includes this product participant 1 times

    # package with product of type "Planning"
    And there is a planning called "Planning meetings 1" with a price of "39"
    And this product planning is assigned to this package
    And there is a product Planning called "Planning meetings 2" with a price of "39" and a max quantity of 5
    And this product planning is assigned to this package
    And there is an attributable option called "Gala dinner" with a price of "139"
    And there is an option called "Chaise" with a price of "10"
    And there is a promotion "Assdays Promotion Code" with code "ASDDAYS30" for product option "Chaise"
    And there is an option called "Table" with a price of "20"
    And there is an option called "Fontaine à eau" no more available
    And there is an option called "Pose moquette" not deletable
    And these options are assigned to this package

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    # several participants are required to test deletion
    And the user "user_asddays_2@proximum.com" is created
    And there is a participant for this sheet and this user
    And the user "user_asddays_3@proximum.com" is created
    And there is a participant for this sheet and this user
    And there is billing info for this sheet
    And there is an order with the amount of 100 for this sheet
    And there is this plan for this order
    And there is this product Participant for this order
    And there is this product Planning for this order
    And there is the option "Chaise" 2 times for this order
    And there is the option "Table" 1 times for this order
    And there is the option "Pose moquette" 3 times for this order
    And there is a paid transaction with reference "ref_1" and amount 100 for this sheet

    And I am logged with "user_asddays_1@proximum.com" on front

    When I am on this page "/fr/sheet"
    And I follow "navigation.links.package.order_summary_total"
    Then I should be on this page "/fr/sheet/1/order/summary"
    And I should see "package.summary.title"
    When I follow "package.summary.edit"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should see "Planning meetings 2"
    And I should see "package.participant.add"
    And I follow "package.participant.add"
    And I should see "sheet.participant.sendInvite"

  Scenario: I can remove two participants from my package (one included and one payed)
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    And I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "package.product.unitPrice"
    When I follow "package.participant.delete"
    Then I should be on this page "/fr/sheet/1/package/step/1/participant/remove"
    And I check "remove_participant_participants_1"
    And I check "remove_participant_participants_2"
    When I press "sheet.participant.remove"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should not see "package.participant.delete"

  Scenario: I can remove one planning
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/1"
    And I should not see "package.participant.delete"
    And the "participant_and_planning[planningQuantity][quantity]" field should contain "1"
    # participant product for the participant id=3
    When I fill in "participant_and_planning[3]" with "2"
    # planning
    And I fill in "participant_and_planning[planningQuantity][quantity]" with "0"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"

  Scenario: I can edit my package option. Remove 2 options chaise and add 1 option A
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And the "options[7][quantity]" field should contain "2"
    And the "options[8][quantity]" field should contain "1"
    When I fill in "options[7][quantity]" with "0"
    And I fill in "options[8][quantity]" with "2"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"

  Scenario: I can see my updated package summary
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I am on this page "/fr"
    When I go to this page "/fr/sheet/1/package/summary"
    Then I should see "package.summary.title"
    # planning supplementaire
    And the "tr[data-product-id='5']" element should contain "-1"
    # option chaise
    And the "tr[data-product-id='7']" element should contain "-2"
    # option A
    And the "tr[data-product-id='8']" element should contain "1"

  Scenario: I can't remove a product that is not deletable or buyable
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.unavailable"
    When I fill in "options[10][quantity]" with "2"
    Then I press "package.product.validate"
    And I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.productNotDeletable"

  Scenario: I can't use a promotion code for a negative product quantity
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/summary"
    And I fill in "package_summary_promotion_code_promotionCode" with "ASDDAYS30"
    And I press "package.summary.promotion.button.label"
    Then I should be on this page "/fr/sheet/1/package/summary#summary-promo-code-row"
    And I should see "flash.package.promotionCode.error.negativeRow"

  Scenario: I can pay my updated package
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr"
    Then I go to this page "/fr/sheet/1/package/summary"
    And I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    When I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/orders"
