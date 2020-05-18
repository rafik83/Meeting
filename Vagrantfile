# -*- mode: ruby -*-
# vi: set ft=ruby :

$script = <<-SCRIPT
printf "deb http://archive.debian.org/debian/ jessie main\ndeb-src http://archive.debian.org/debian/ jessie main\ndeb http://security.debian.org jessie/updates main\ndeb-src http://security.debian.org jessie/updates main" > /etc/apt/sources.list
echo '' > /etc/apt/sources.list.d/deb_debian_org_debian.list
apt-get -o Acquire::Check-Valid-Until=false update
SCRIPT

app = {
  :name        => 'vimeet',
  :box         => 'manala/app-dev-debian',
  :box_version => '~> 3.0.0',
  :box_memory  => 2048
}

Vagrant.require_version '>= 2.0.1'

Vagrant.configure(2) do |config|

  # Ssh
  config.ssh.username      = 'app'
  config.ssh.forward_agent = true

  # Vm
  config.vm.box           = app[:box]
  config.vm.box_version   = app[:box_version]
  config.vm.hostname      = app[:name] + '.proximum'
  config.vm.network       'private_network', type: 'dhcp'
  config.vm.define        'localhost' do |localhost| end
  config.vm.synced_folder '.', '/srv/app',
    type: 'nfs',
    mount_options: ['nolock', 'actimeo=1', 'fsc']

  # Vm - Provider - Virtualbox
  config.vm.provider 'virtualbox' # Force provider
  config.vm.provider :virtualbox do |virtualbox|
    virtualbox.name   = app[:name]
    virtualbox.memory = app[:box_memory]
    virtualbox.customize ['modifyvm', :id, '--natdnshostresolver1', 'on']
    virtualbox.customize ['modifyvm', :id, '--natdnsproxy1', 'on']
  end

  config.vm.provision "shell", inline: $script

  # Vm - Provision - Dotfiles
  for dotfile in ['.ssh/config', '.gitconfig', '.gitignore', '.composer/auth.json']
    if File.exists?(File.join(Dir.home, dotfile)) then
      config.vm.provision dotfile, type: 'file', run: 'always' do |file|
        file.source      = '~/' + dotfile
        file.destination = '/home/' + config.ssh.username + '/' + dotfile
      end
    end
  end

  # Vm - Provision - Setup
  for playbook in ['ansible', 'app']
    config.vm.provision playbook, type: 'ansible_local' do |ansible|
      ansible.version            = (playbook == 'ansible') ? 'latest' : ''
      ansible.compatibility_mode = '2.0'
      ansible.provisioning_path  = '/srv/app/ansible'
      ansible.playbook           = playbook + '.yml'
      ansible.inventory_path     = '/etc/ansible/hosts'
      ansible.tags               = ENV['ANSIBLE_TAGS']
      ansible.extra_vars         = JSON.parse(ENV['ANSIBLE_EXTRA_VARS'] || '{"manala":{"update":true}}')
    end
  end

  # Plugins - Landrush
  if Vagrant.has_plugin?('landrush')
    config.landrush.enabled            = true
    config.landrush.tld                = config.vm.hostname
    config.landrush.guest_redirect_dns = false
  end

end
