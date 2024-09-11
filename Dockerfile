#Dockerfile configured by Irsal Abu Aisyah (alfatax)

FROM ubuntu/apache2:2.4-20.04_beta

# Menambahkan konfigurasi ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Update package lists dan install PHP 5.6 dan modul-modulnya
RUN apt-get update && apt-get install -y \
    software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update && \
    apt-get install -y \
    php5.6 \
    libapache2-mod-php5.6 \
    php5.6-curl \
    php5.6-gd \
    php5.6-mbstring \
    php5.6-mcrypt \
    php5.6-mysql \
    php5.6-xml \
    php5.6-xmlrpc && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Enable PHP 5.6 module
RUN a2enmod php5.6

# Enable necessary modules
RUN a2enmod proxy \
    && a2enmod proxy_http \
    && a2enmod rewrite


# Install screen
#RUN apt-get update && apt-get install -y screen

# Perbarui daftar paket dan instal iproute2
RUN apt-get update && apt-get install -y iproute2

# Istall IPtables
RUN apt-get update && apt-get install -y iptables

# Perbarui daftar paket dan instal iproute2
RUN apt-get update && apt-get install -y nano

#install curl
RUN apt-get install curl

#install net tools
RUN apt-get install net-tools

# Start Apache in the foreground
CMD ["apachectl", "-D", "FOREGROUND"]