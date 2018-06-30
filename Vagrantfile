def which(cmd)
  exts = ENV['PATHEXT'] ? ENV['PATHEXT'].split(';') : ['']
  ENV['PATH'].split(File::PATH_SEPARATOR).each do |path|
    exts.each { |ext|
      exe = File.join(path, "#{cmd}#{ext}")
      return exe if File.executable? exe
    }
  end
  return nil
end

Vagrant.configure(2) do |config|
  config.vm.provider "virtualbox" do |v|
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant", "1"]
  end

  config.vm.box     = "mrlesmithjr/yakkety64"
  config.vm.network 'private_network', ip: '192.168.255.2'
  config.vm.host_name = "dev.phing.org"
  config.vm.synced_folder "./", "/vagrant", id: "vagrant-root", owner: "vagrant", group: "www-data"
  config.vm.synced_folder '~/.composer', '/home/vagrant/.composer', id: "composer", owner: "vagrant", group: "www-data"
  config.vm.provision :shell, inline: %q{echo "export VAGRANT_VERSION=2" >> /home/vagrant/.bashrc}

  if which('ansible-playbook')
      config.vm.provision "ansible" do |ansible|
        ansible.playbook = "ansible/playbook.yml"
        ansible.extra_vars = {
          ansible_python_interpreter: "/usr/bin/python3"
        }
        ansible.tags     = "vagrant"
      end
  else
    config.vm.provision 'shell', path: "ansible/windows.sh"
  end
end
