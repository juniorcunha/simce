# SiMCE - Sistema de Investigação e Monitoramento Centralizado

### Rede ###
yum install system-config-network-tui dnsmasq
 
### Repos ###
rpm --import http://repository.it4i.cz/mirrors/repoforge/RPM-GPG-KEY.dag.txt
yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
yum install http://packages.asterisk.org/centos/6/current/x86_64/RPMS/asterisknow-version-3.0.1-3_centos6.noarch.rpm
yum install http://repository.it4i.cz/mirrors/repoforge/redhat/el6/en/x86_64/rpmforge/RPMS/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
yum install http://www.percona.com/downloads/percona-release/redhat/0.1-3/percona-release-0.1-3.noarch.rpm

### Asterisk ###
yum -y install vim wget ntsysv ntp tree openssh-clients rsync
ntpdate br.pool.ntp.org br.pool.ntp.org br.pool.ntp.org
yum --enablerepo=asterisk-11 -y install asterisk asterisk-configs dahdi-linux dahdi-tools libpri

### Driver Khomp ###
yum -y install cpp cloog-ppl gcc gcc-c++ kernel-devel libstdc++-devel mpfr ppl unzip dialog zip  p7zip p7zip-plugins
gunzip channel_4.2_002_x86-64.sh.gz
./channel_4.2_002_x86-64.sh

### MySQL ###
yum -y install Percona-XtraDB-Cluster-full-56
# rpm -e mysql-libs
 
### WEB ###
yum -y install apr apr-util apr-util-ldap httpd httpd-tools mailcap mod_ssl perl-DBD-MySQL perl-DBI php php-cli php-common php-gd php-mbstring php-mysql php-pdo php-xml php-gd 

### Wav2png ###
yum -y install alsa-lib boost boost-date-time boost-devel boost-filesystem boost-graph boost-iostreams boost-math boost-program-options boost-python boost-regex boost-serialization boost-signals boost-system boost-test boost-thread boost-wave flac libicu libogg libpng  libpng-devel libsndfile libsndfile-devel libvorbis zlib-devel 

wget http://download.savannah.nongnu.org/releases/pngpp/png++-0.2.5.tar.gz
make
make install

wget https://github.com/beschulz/wav2png/archive/master.zip
cd build
make all

### VoiceID ###
yum -y install libmodplug a52dec amrwb atk avahi-libs cairo cdparanoia-libs ConsoleKit ConsoleKit-libs cups-libs dbus dirac-libs eggdbus exempi faac faad2-libs ffmpeg-compat fontconfig freetype GConf2 giflib gnome-keyring gstreamer gstreamer-plugins-bad-free gstreamer-plugins-base gstreamer-plugins-good gstreamer-plugins-ugly gstreamer-tools gtk2 hal-libs hicolor-icon-theme iso-codes jasper-libs java-1.7.0-openjdk jline jpackage-utils lame libao libasyncns libavc1394 libcdaudio libcroco libdc1394 libdca libdv libdvdread libexif libfontenc libgsf libgudev1 libICE libid3tag libIDL libiec61883 libiptcdata libjpeg-turbo libkate libmad libmms libmodplug libmpcdec libmpeg2 libmusicbrainz liboil libquicktime libraw1394 librsvg2 librtmp libsamplerate libshout libSM libsoup libthai libtheora libtiff libtool-ltdl libudev libusb1 libv4l libva libvisual libvpx libX11 libX11-common libXau libXaw libxcb libXcomposite libXcursor libXdamage libXext libXfixes libXfont libXft libXi libXinerama libXmu libXpm libXrandr libXrender libXt libXtst libXv libXxf86vm mesa-dri1-drivers mesa-dri-drivers mesa-dri-filesystem mesa-libGL mesa-private-llvm mjpegtools opencore-amr openjpeg-libs ORBit2 orc pango pixman polkit pulseaudio-libs python-setuptools rhino schroedinger SDL SDL_gfx sgml-common sox taglib ttmkfdir twolame-libs tzdata-java  wavpack x264 x264-libs xml-common xorg-x11-fonts-Type1 xorg-x11-font-utils xvidcore

wget https://github.com/google-code-export/voiceid/archive/master.zip
python setup.py install

asterisk  ALL=(root) NOPASSWD:/opt/simce/scripts/simce-voiceid.php

### Audio ###
yum -y nodejs npm

### NTP ###
yum -y install ntp

### Nagios ###
yum -y install nagios-nrpe nagios-plugins-nrpe nagios-plugins-all
