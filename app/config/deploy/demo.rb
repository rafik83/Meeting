set :domain,    "vimeet.proximum.elao.ninja.local"
set :deploy_to, "/srv/app/symfony"
set :user,      "deploy"

# Options to pass to composer when installing/updating
set :composer_options, "--verbose --prefer-dist --optimize-autoloader --no-progress"

# Clear *_dev controllers
set :clear_controllers, false

role :app, domain, :primary => true

# Scm
set :branch, "master"
