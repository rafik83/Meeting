set :front1,    "proximum-web-apache-01"
set :front2,    "proximum-web-apache-02"
set :deploy_to, "/var/www/proximum-vimeet.project.local/htdocs"
set :user,      "www-data"

# Options to pass to composer when installing/updating
set :composer_options, "--verbose --prefer-dist --optimize-autoloader --no-progress"

# Clear *_dev controllers
set :clear_controllers, false

role :app, front1, :primary => true
#role :app, front2, :primary => false

namespace :app_tasks do
  task :php do
    capifony_pretty_print "--> Restarting PHP"
    invoke_command "sudo /usr/sbin/service php5-fpm reload", :via => run_method
    capifony_puts_ok
  end
end

namespace :deploy do
  task :set_permissions, :roles => :app, :except => { :no_release => true } do
    # do not set permissions on prod
  end
end

# Scm
set :branch, "prod"

# Tasks
after :deploy, 'app_tasks:initdb'
