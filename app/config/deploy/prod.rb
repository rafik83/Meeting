set :front1,    "vimeet-prod1"
set :front2,    "vimeet-prod2"
set :deploy_to, "/var/www/proximum-vimeet.project.local/htdocs"
set :user,      "www-data"

set :deploy_via, :copy

# Options to pass to composer when installing/updating
set :composer_options, " --no-interaction --verbose --prefer-dist --optimize-autoloader --no-progress"

# Clear *_dev controllers
set :clear_controllers, true

role :app, front1, :primary => true
role :app, front2, :primary => false

namespace :deploy do
  task :set_permissions, :roles => :app, :except => { :no_release => true } do
    # do not set permissions on prod
  end
end

# Scm
set :branch, "prod"
