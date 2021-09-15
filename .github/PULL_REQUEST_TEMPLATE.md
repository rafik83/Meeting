Tasks list:

- [ ]
- [ ]
- [ ]

Tasks to run after deploying on preprod or prod:

- [ ] Reindex Elasticsearch: `$ bin/console fos:elastica:populate --env=prod --no-debug`
- [ ] Run the whatever calculator : `$ bin/console vimeet:whatever-command`
- [ ] ...

Definition of Done:

- [ ] 1. Check access to controllers (if concerned)
- [ ] 2. Respect the UI Admin (if the feature concerns the Admin)
- [ ] 3. Generate DB migration if the structure changes (`$ make migrations`)
- [ ] 4. Re-check Acceptance test: proofreading the story at the development end
- [ ] 5. Unit tests pass
- [ ] 6. Functional tests pass
- [ ] 7. No conflict with `master` or solve them as soon as possible.
- [ ] 8. Create translation keys in French and listed alphabetically
- [ ] 9. Copy/Paste all new translations keys in the corresponding user story on Jira
- [ ] 10. Add a line in `CHANGELOG.md` / `Unreleased`
- [ ] 11. To be reviewed (have at least one "approve") in order to pass the feature to "preprod"
