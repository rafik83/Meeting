set :domain,    "vimeet-preprod"
set :deploy_to, "/var/www/proximum-vimeet.project.local/htdocs"
set :user,      "www-data"

# Options to pass to composer when installing/updating
set :composer_options, "--no-interaction --verbose --prefer-dist --optimize-autoloader --no-progress"

# Clear *_dev controllers
set :clear_controllers, false

set :keep_releases, 1

role :app, domain, :primary => true

# Scm
set :branch, "preprod"
