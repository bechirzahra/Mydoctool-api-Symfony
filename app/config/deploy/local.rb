server 'localhost', :app, :web, :primary => true

set :symfony_env_prod,  "prod"
set :deploy_via,        :remote_cache
set :user,              fetch(:user, "morgangiraud")
set :branch,            fetch(:branch, "master")
set :interactive_mode,  true

set :default_environment, {
  'PATH' => '/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin:/opt/X11/bin'
}

set :shared_files,      ["app/config/parameters.yml", "app/config/parameters_local.yml"]
set :shared_children,   ["app/logs", "app/sessions"]

set :deploy_to,   "#{ENV['HOME']}/Sites/local/#{application}.com"

set :assets_install, true
set :dump_assetic_assets, true

namespace :mydoctool do
    task :clean_cache_and_logs, :roles => :app do
        capifony_pretty_print "--> cleaning cache and logs directories"

        run "cd #{latest_release} && rm -rf app/cache/* app/logs/*"
        capifony_puts_ok
    end
end

after "symfony:project:clear_controllers", "mydoctool:clean_cache_and_logs"