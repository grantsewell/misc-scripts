#!/bin/bash
# Title:  Debian VM Build Script
# Author: Grant Sewell
# Date:   04/14/2022

# Update cache and upgrade system
apt-get update
apt-get upgrade -y
apt-get dist-upgrade -y

# Remove VIM Tiny
apt-get remove vim-tiny -y

# Install Standard Utilities
apt-get install vim htop open-vm-tools sudo nfs-kernel-server unzip ntfs-3g git openssh-server apt-transport-https curl gnupg2 unzip unattended-upgrades cowsay -y

# Cleanup leftover applications
apt-get autoremove -y

# Blacklist Unnecessary Functions for ESXi
cat >/etc/modprobe.d/blacklist.conf <<EOL
#Optimized Blacklist for Debian VM
blacklist floppy
blacklist mptctl
blacklist pcspkr
blacklist snd_pcm
blacklist snd_page_alloc
blacklist snd_timer
blacklist snd
blacklist soundcore
blacklist coretemp
blacklist parport
blacklist parport_pc
blacklist i2c-piix4
EOL

# Disable IPv6
cat >>/etc/sysctl.conf <<EOL
# Disable IPv6
#
net.ipv6.conf.all.disable_ipv6 = 1
net.ipv6.conf.default.disable_ipv6 = 1
net.ipv6.conf.lo.disable_ipv6 = 1
net.ipv6.conf.eth0.disable_ipv6 = 1
EOL

# Configure Unattended Upgrades
cat >>/etc/apt/apt.config.d/51my-unattended-upgrades <<EOL
Unattended-Upgrade::Allowed-Origins {
"${distro_id}:${distro_codename}-updates";
};
EOL

# Configure Upgrade Frequency
cat >>/etc/apt/apt.conf.d/20auto-upgrades <<EOL
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOL

# Enable Unattended Upgrades
systemctl enable unattended-upgrades
systemctl start unattended-upgrades

#Update initramfs
update-initramfs -u

# Add User to Sudoers
read -p "Enter Username to add to sudoers: " user_sudo
adduser $user_sudo sudo

# End of Script
echo "Installation Complete"
read -t 5 -p "System will reboot in 5 seconds..."
reboot
