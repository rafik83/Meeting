set :application, "vimeet"

# Multistage
set :stages,        %w(demo prod)
set :default_stage, "demo"
set :stage_dir,     app_config_path + "/deploy"
require 'capistrano/ext/multistage'

# Ssh
ssh_options[:forward_agent] = true

# Scm
set :scm,          :git
set :scm_verbose,  true
set :scm_username, "git"
set :repository,   "git@github.com:proximum/vimeet.git"
set :deploy_via,   :remote_cache

# Vendors
set :copy_vendors,   true
set :update_vendors, false

# Composer
set :use_composer,     true
set :use_composer_tmp, false
set :composer_bin,     "composer"

# Directory structure
set :symfony_console, "bin/console"
set :app_path,        "app"
set :app_config_path, app_path + "/config"
set :app_config_file, "parameters.yml"
set :app_web,         "web"
set :log_path,        "var/logs"
set :cache_path,      "var/cache"

# Shared
set :shared_files,    [app_config_path + "/" + app_config_file]
set :shared_children, ["web/uploads", "node_modules", log_path, "web/css"]

# Assets
set :dump_assetic_assets,        false
set :normalize_asset_timestamps, false

# Permissions
set :use_sudo,            false
set :permission_method,   :acl
set :use_set_permissions, true
set :group_writable,      true
set :webserver_user,      "www-data"
set :writable_dirs,       [log_path, cache_path, "web/uploads", "web/css"]

# Database
set :model_manager, "doctrine"
set :backup_path,   "var/backups"

# Releases
set :keep_releases, 3

# Tasks
after 'symfony:cache:warmup', 'app_tasks:install'
after :deploy, 'app_tasks:php'
after :deploy, 'deploy:cleanup'

namespace :app_tasks do
  task :initdb do
      capifony_pretty_print "--> Init DB"
      invoke_command "cd #{latest_release} && make init-db", :via => run_method
      capifony_puts_ok
  end
  task :php do
      capifony_pretty_print "--> Restarting PHP"
      invoke_command "sudo service php5-fpm restart", :via => run_method
      capifony_puts_ok
  end
  task :install do
      capifony_pretty_print "--> Installing"
      invoke_command "cd #{latest_release} && make install@prod", :via => run_method
      capifony_puts_ok
  end
end

# Be more verbose by uncommenting the following line
# IMPORTANT = 0
# INFO      = 1
# DEBUG     = 2
# TRACE     = 3
# MAX_LEVEL = 3
logger.level = Logger::MAX_LEVEL
