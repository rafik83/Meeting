@admin @admin-event

Feature: Duplicate event
  I need to be able to duplicate an event

  Scenario: Duplicate event
    Given the database is purged
    And the event "Les rendez-vous CARNOT 2016" is created
    And the event "Foire à l'oignon" is created
    And the super admin "test@test.com" is created
    And I am logged with this admin
    When I go to this page "/fr/event/duplicate"
    Then I should see "admin.event.duplicate.title"
    When I check radio "event_duplicate_event_0"
    And I press "form.event_duplicate.children.submit.label"
    Then I should be on this page "/fr/event/create/from/1"
    When I fill in the following:
      | form.event_create.children.domain.label | new.super.event |
    And I press "form.event_create.children.submit.label"
    Then I should see "flash.admin.event.duplicate.warning"
    And I should see "admin.event.update.origin"
    When I fill in the following:
      | event_update_translations_fr_description | LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE                                  |
      | event_update_translations_en_description | In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion. |
    And I press "form.event_update.children.submit.label"
    Then I should see "flash.admin.event.update.success"
