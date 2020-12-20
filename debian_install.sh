#!/bin/sh
# Title:  Debian VM Build Script
# Author: Grant Sewell
# Date:   12/19/2020

apt-get update

# Remove VIM Tiny
apt-get remove vim-tiny -y

# Install Standard Utilities
apt-get install vim htop open-vm-tools sudo nfs-kernel-server unzip ntfs-3g git openssh-server apt-transport-https curl gnupg2 unzip -y

# Blacklist Unnecessary Functions for ESXi
cat >/etc/modprobe.d/blacklist.conf <<EOL
line 1, #Optimized Blacklist for Debian VM
line 2, blacklist floppy
line 3, blacklist mptctl
line 4, blacklist pcspkr
line 5, blacklist snd_pcm
line 6, blacklist snd_page_alloc
line 7, blacklist snd_timer
line 8, blacklist snd
line 9, blacklist soundcore
line 10, blacklist coretemp
line 11, blacklist parport
line 12, blacklist parport_pc
line 13, blacklist i2c-piix4
... 
EOL

# Disable IPv6
echo '# Disable IPv6' >> /etc/sysctl.conf
echo '#' >> /etc/sysctl.conf
echo 'net.ipv6.conf.all.disable_ipv6 = 1' >> /etc/sysctl.conf
echo 'net.ipv6.conf.default.disable_ipv6 = 1' >> /etc/sysctl.conf
echo 'net.ipv6.conf.lo.disable_ipv6 = 1' >> /etc/sysctl.conf
echo 'net.ipv6.conf.eth0.disable_ipv6 = 1' >> /etc/sysctl.conf
