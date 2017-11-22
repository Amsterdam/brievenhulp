# Brievenhulp: Snap de brief!

Snap de Brief is een project van de Gemeente Amsterdam. Meer informatie over dit project is te vinden op de website van het [Datalab van de Gemeente Amsterdam](http://www.datalabamsterdam.nl)

Meer informatie [datapunt.ois@amsterdam.nl](datapunt.ois@amsterdam.nl)


## Waarom is deze code gedeeld

Het FIXXX-team van de Gemeente Amsterdam ontwikkelt software voor de gemeente.
Veel van deze software wordt vervolgens als open source gepubliceerd zodat andere
gemeentes, organisaties en burgers de software als basis en inspiratie kunnen 
gebruiken om zelf vergelijkbare software te ontwikkelen.
De Gemeente Amsterdam vindt het belangrijk dat software die met publiek geld wordt
ontwikkeld ook publiek beschikbaar is.

## Onderhoud en security

Deze repository bevat een "as-is" kopie van het project op moment van publiceren.
Deze kopie wordt niet actief onderhouden.

## Wat mag ik met deze code

De Gemeente Amsterdam heeft deze code gepubliceerd onder de Mozilla Public License v2.
Een kopie van de volledige licentie tekst is opgenomen in het bestand LICENSE.

Het FIXXX-team heeft de verdere doorontwikkeling van deze software overgedragen 
aan de probleemeigenaar. De code in deze repository zal dan ook niet actief worden
bijgehouden door het FIXXX-team.

## Open Source

Dit project maakt gebruik van diverse andere Open Source software componenten. O.a. 
[Symfony](http://www.symfony.com), 
[Doctrine](http://www.doctrine-project.org/), 
[Composer](https://getcomposer.org/), 
[Monolog](https://github.com/Seldaek/monolog), 
[Twig](http://twig.sensiolabs.org/), 
[Swiftmailer](http://swiftmailer.org/), 
[LiipImagine](https://github.com/liip/LiipImagineBundle), 
[Jsqueeze](https://github.com/tchwork/jsqueeze), 
[scssphp](http://leafo.net/scssphp/), 
[PasswordStrengthBundle](https://github.com/rollerworks/PasswordStrengthBundle), 
[Intervention Image](http://image.intervention.io/), 
[MessageBird API](https://www.messagebird.com/developers/php), 
[JavaScript Canvas to Blob]( https://github.com/blueimp/JavaScript-Canvas-to-Blob), 
[JavaScript Load Image](https://github.com/blueimp/JavaScript-Load-Image), 
[Awesomplete](https://leaverou.github.io/awesomplete/), 
[HTML5 Shiv](https://github.com/aFarkas/html5shiv), 
[jQuery leanModal](http://leanmodal.finelysliced.com.au/)


## Installeren

Om deze software te draaien moet je beschikking hebben over een webserver met PHP
(Apache, Nginx of IIS) en een PostgreSQL databaseserver.

Om de SMS functionaliteit te kunnen gebruiken heb je een contract nodig met een 
SMS service provider. Geïmplementeerd is de provider [MessageBird](https://www.messagebird.com/nl/).

Maak een nieuwe PostgreSQL database aan voor dit project. Voer met een superuser
onderstaand statement uit om de UUID functies beschikbaar te maken.

    CREATE EXTENSION "uuid-ossp";
    
Maak een clone van de code.

    git clone git@bitbucket.org:datapunt/brievenhulp.git
    cd brievenhulp
    composer install
  
Afhankelijk van je systeem en de rechtenstructuur moet je sommige directories 
beschrijfbaar maken. Zie ook [Setting up Permissions in de handleiding van Symfony 3.0](http://symfony.com/doc/3.0/book/installation.html#checking-symfony-application-configuration-and-setup).
De volgende directories moeten schrijfbaar zijn voor Symfony

* var/cache/*
* var/logs/*
* var/sessions/*
* var/temp/*
* var/data/*

Installeer composer en voer een composer install uit

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Voer Doctrine Migrations uit om de database te initialiseren
    
    php app/console doctrine:migrations:migrate

Voer tenslote een cache clear uit voor dev en prod om zeker te weten dat alle cache gereed is.    

    php app/console cache:clear --env=dev
    php app/console cache:clear --env=prod
    
Configueer tenslote een vhost van de webserver. Zie ook de specifieke handleiding 
per webserver [in de Symfony 3.0 handleiding](http://symfony.com/doc/3.0/cookbook/configuration/web_server_configuration.html)

## Cronjobs

Stel de volgende cron job in, draai deze met een hoge frequentie (bijvoorbeeld elke minuut)

    php bin\console brievenhulp:assign-hulpverlener

Stel de volgende cron job in, draai deze met een lage frequentie (bijvoorbeeld dagelijkse)
    
    php bin\console brievenhulp:cleanup-hulpvragen

## Command line commands

Een wachtwoord instellen voor een gebruiker

    php bin\console brievenhulp:set-password email password
    
Een nieuwe gebruiker maken

    php bin\console brievenhulp:create-user naam organisatie telefoon email password role

