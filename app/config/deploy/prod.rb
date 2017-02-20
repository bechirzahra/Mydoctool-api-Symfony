# server 'mydoctool.com', :app, :web, :primary => true
server '51.255.39.42', :app, :web, :primary => true

set :symfony_env_prod, "prod"
set :deploy_via, :remote_cache #Avoid to clone the whole repository each time
set :user, "root"
set :branch, fetch(:tag, 'master')

set :shared_files,        ["app/config/parameters.yml", "app/config/parameters_prod.yml", "app/var/jwt/private.pem", "app/var/jwt/public.pem"]
set :shared_children,       ["app/logs", "app/sessions", "web/uploads"]

set :deploy_to,   "/var/www/#{application}.com"

ssh_options[:keys] = ["#{Dir.pwd}/server/mydoctool_rsa"]

set :assets_install, true
set :dump_assetic_assets, true

namespace :mydoctool do
    task :clean_cache_and_logs, :roles => :app do
        capifony_pretty_print "--> cleaning cache and logs directories"

        run "cd #{latest_release} && rm -rf app/cache/* app/logs/*"
        capifony_puts_ok
    end
end

namespace :deploy do
  # php-fpm-5.5 needs to be restarted to make sure that the APC cache is cleared.
  # This overwrites the :restart task in the parent config which is empty.
  desc "Restart php5-fpm"
  task :restart, :except => { :no_release => true }, :roles => :app do
    run "service php5-fpm restart"
    puts "--> php5-fpm successfully restarted".green
  end
end

after "symfony:project:clear_controllers", "mydoctool:clean_cache_and_logs"
