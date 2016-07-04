set :domain,    "proximum-vimeet-preprod"
set :deploy_to, "/var/www/proximum-vimeet.project.local/htdocs"
set :user,      "www-data"

# Options to pass to composer when installing/updating
set :composer_options, "--verbose --prefer-dist --optimize-autoloader --no-progress"

# Clear *_dev controllers
set :clear_controllers, false

role :app, domain, :primary => true

namespace :app_tasks do
  task :php do
    capifony_pretty_print "--> Restarting PHP"
    invoke_command "sudo /usr/sbin/service php5-fpm reload", :via => run_method
    capifony_puts_ok
  end
end

# Scm
set :branch, "preprod"

# Tasks
after :deploy, 'app_tasks:initdb'
