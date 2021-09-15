@admin @sheet @mail

Feature: See sheet details
  As an admin, I can see the details of a sheet

Background:
    Given the database is purged
    And the event "SymfonyCon" is created
    And the domain for this event is "symfonycon.vimeet.proximum"
    And there is a type "Visiteur" in this event
    And the user "fabien@sensio.com" is created
    And this user is called "Fabien" "Potencier"
    And there is a sheet for this type with the title "Sensio"
    And there is a participant for this sheet and this user
    And this participant is registered
    And this participant has visio option activated
    And this sheet is validated
    And there is a type "Exposant" in this event
    And there is a sheet for this type with the title "Heroku"
    And there is billing info for this sheet
    And there is an order with the amount of 42 for this sheet
    And there is an invoice with the numero "Vi2017-0001" for this sheet
    And elastica is populate
    And I am logged as admin
    And I go to "/fr/event"
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on this page "/fr/event/1/sheet"

  Scenario: I can see the details of a sheet
    Then I should see "Sensio"
    When I follow "Sensio"
    Then I should see "Sensio"
    And I should see "Fabien"
    And I should see "POTENCIER"
    And I should see "admin.sheet.details.meeting.request.approved"
    And I should see "admin.sheet.details.meeting.request.pending"
    And I should see "admin.sheet.details.meeting.request.refused"
    And I should see "admin.sheet.details.meeting.proposition.refused"
    And I should see "admin.sheet.details.company.title"
    And I should see "Chiffre d'affaires"
    And I should see "2 M€"
    And I should see "Nom (Société / Organisme)"
    # todo: verifier l'interet de tester ces élements, qui sont similaires aux 2 précédants
    # And I should see "Ville"
    # And I should see "Adresse"
    # And I should see "Site internet"
    And the "is-visio" checkbox should be checked

  Scenario: I can add a comment on a sheet
    When I follow "Sensio"
    And I fill in the following:
      | sheet_comment_text | This is a test |
    And I press "form.sheet_comment.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.add_comment.success"
    And I should see "This is a test"
    And I should see "admin.sheet.details.comments.author"

  Scenario: I can change the sheet type
    When I follow "Sensio"
    Then the ".label-sheet-type" element should contain "Visiteur"
    And I should not see "admin.sheet.trace.changed_type"
    When I check the "Exposant" radio
    And I press "form.change_type.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.admin.sheet.change_type.success"
    And the "sheet.changed_type" mail should be sent to "fabien@sensio.com" from "no-reply@symfonycon.vimeet.proximum"
    And the "sheet.changed_type" mail should be sent in bcc to "team-project@example.net" from "no-reply@symfonycon.vimeet.proximum"
    And the ".label-sheet-type" element should contain "Exposant"
    And I should see "admin.sheet.trace.changed_type"

  Scenario: I cant change a sheet type and I can see generated invoice, and cannot edit order when sheet has at least one invoiced order
    When I follow "Heroku"
    Then I should see "Heroku"
    Then I should not see "form.change_type.children.submit.label"
    And I should see "Vi2017-0001"
    And I should not see "admin.order_edit.link"
