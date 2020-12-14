FROM php:7.4

#install software-properties to add ppa repository
RUN apt-get update && apt-get install -y software-properties-common
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

#RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
#    install-php-extensions pdo_mysql xdebug
RUN docker-php-ext-install mysqli pdo pdo_mysql
#RUN add-apt-repository ppa:ondrej/php && apt-get update  

#install php7.3
#RUN apt-get install -y php7.3 curl php7.3-xml php7.3-mysql

#install node and npm

#make room for app
RUN mkdir -p subscription-management
COPY . /subscription-management

#build vue project
RUN cd ./subscription-management