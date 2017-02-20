set :stages, %w(prod local)
set :default_stage, "local"
set :stage_dir, 'app/config/deploy'
require 'capistrano/ext/multistage'

set :application, "mydoctool"

ssh_options[:forward_agent] = true
default_run_options[:pty] = true

set :writable_dirs,     ["app/cache", "app/logs"]
set :permission_method, :chmod
set :use_sudo, false

set :app_path,    "app"

set :repository,  "git@bitbucket.org:TomOlivier/mydoctool-api.git"
set :scm,         :git
set :scm_verbose, true

set :model_manager, "doctrine"

set :use_composer, true
set :composer_options,  "--no-dev --verbose --prefer-dist --optimize-autoloader --no-progress"
set :update_vendors, false #get the last version of vendors

set  :keep_releases,  3

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL