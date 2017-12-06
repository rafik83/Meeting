Tasks list:

- [ ]
- [ ]
- [ ]

Tasks to run after deploying on preprod or prod:
- [ ] Reindex Elasticsearch: `$ bin/console fos:elastica:populate --env=prod --no-debug`
- [ ] Run the whatever calculator : `$ bin/console vimeet:whatever-command`
- [ ] ...

Definition of Done:

- [ ] 1. Create translation keys in French and listed alphabetically
- [ ] 2. Check access to controllers (if concerned)
- [ ] 3. Respect the UI Admin (if the feature concerns the Admin)
- [ ] 4. Generate DB migration if the structure changes (`$ make migrations`)
- [ ] 5. Regenerate npm-shrinkwrap.json if a new npm package is installed: (`$ npm shrinkwrap`)
- [ ] 6. Re-check Acceptance test: proofreading the story at the development end
- [ ] 7. Unit tests pass
- [ ] 8. Functionnal tests pass
- [ ] 9. No errors on Insight
- [ ] 10. No conflict with `master` or solve them as soon as possible.
- [ ] 11. To be reviewed (have at least one "approve") in order to pass the feature to "preprod"
- [ ] 12. Copy/Paste all new translations keys in the corresponding user story on Jira

*Cross out unrelevant item of the DoD (items 1 to 5 only), example:*
- ~~Regenerate npm-shrinkwrap~~
