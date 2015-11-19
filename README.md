#Jenkins 

## Table of contents

<!-- MarkdownTOC depth=3 autoanchor=true bracket=round -->

- Installation auf Ubuntu 14.03.2 LTS (Codename: trusty64)
    - [Installation](#installation)
        - prerequisites
        - install Jenkins
        - some usefull notes
        - start configuration
        - jenkins browser commands
        - workaround for svn connection problems
        - fixing timezone-differences
    - [JavaScript WebdriverIO-Testrunner with Mocha](#javascript)
        - Plugins in Jenkins IDE required
        - Packages to install globally on jenkins server
        - fixing xunit bug in webdriverio (not fixed by: 2.Oktober 2015)
        - configure jenkins IDE
        - create build project
        - Source-Code-Managment
        - Post-Build-actions
    - [Testing with Mocha(Chai), WebdriverIO and PhantomJS](#phantomjs)
        - Jenkins configuration
        - Configure Build
        - the respective config file `wdio.conf.js`
        - Testing with ES6 generators (will reduce the callback-hell... ;))
        - the results will be published in `/tests/results/*.xml`
- [PHP](#php)
    - [Ant Version](#phpant)
        - install phpunit
        - install Ant if not done by Jenkins
        - otherwise go to Jenkins and configure Ant-Installation
- Plugins for php
    - [PHPunit](#phpunit)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - configuration file (`/phpunit.xml`)
        - jenkins post-build job configuration
    - [PHP_CodeSniffer](#phpcs)
        - installation on server
        - check which coding standards are installed
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - configuration file (under `build/ruleset.xml`)
        - jenkins post-build job configuration
    - [PHP_loc - lines of code](#phploc)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - jenkins post-build job configuration
    - [PHP_Depend](#phpdepend)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - jenkins post-build job configuration
        - integrate Jdependend SVG reports
    - [PHPMD - PHP Mess Detector](#phpmd)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - configuration file (under `build/phpmd.xml`)
        - jenkins post-build job configuration
    - [PHPCPD - Copy-Paste-Detector](#phpcpd)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - jenkins post-build job configuration
    - [PHP_dox](#phpdox)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - configuration file (under `build/phpdox.xml`)
        - jenkins post-build job configuration
    - PHP_CodeBrowser (not in use!)
    - [PHPMetrics](#phpmetrics)
        - installation on server
        - jenkins plugin to visualize
        - ant target in `build.xml`
        - jenkins post-build job configuration
    - [complete working solution `build.xml`](#phpcompleteant)
    - [Gradle Version](#phpcompletegradle)
- [Java](#java)
    - [Ant Version](#javaant)
    - download following .jar
    - ISSUES
        - [full `build.xml`](#javacompleteant)
        - [full `build.gradle`](#javacompletegradle)

<!-- /MarkdownTOC -->


<a name="None"></a>
# Installation auf Ubuntu 14.03.2 LTS (Codename: trusty64)

<a name="installation"></a>
## Installation[installation]

<a name="None"></a>
### prerequisites

JDK and JRE 
        
    java -version
    java version "1.7.0_79"
    OpenJDK Runtime Environment (IcedTea 2.5.6) (7u79-2.5.6-0ubuntu1.14.04.1)
    OpenJDK 64-Bit Server VM (build 24.79-b02, mixed mode)
        
download from http://openjdk.java.net/install/
        
    sudo apt-get install openjdk-7-jre
    sudo apt-get install openjdk-7-jdk
        
<a name="None"></a>
### install Jenkins 
([see website](https://wiki.jenkins-ci.org/display/JENKINS/Installing+Jenkins+on+Ubuntu))  
        
    wget -q -O - https://jenkins-ci.org/debian/jenkins-ci.org.key | sudo apt-key add -
    sudo sh -c 'echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list'
    sudo apt-get update
    sudo apt-get install jenkins
    
<a name="None"></a>
### some usefull notes    
Jenkins will be launched as a aemon up on start. See `etc/init.d/jenkins` for more details
The 'jenkins' user is created to run this service
**Log files** will be placed in `/var/log/jenkins/jenkins.log`. Check this if you need troubleshooting Jenkins
`/etc/default/jenkins` will capture configuration parameters for the launch like e.g. `JENKINS_HOME`
    
<a name="None"></a>
### start configuration
By default, Jenkins will listen to port **8080** (e.g. http://jenkins-test.local:8080)
Access this port with your browser to **start the configuration**
    
<a name="None"></a>
### jenkins browser commands
    
    http://jenkins-installation:8080/restart

    http://jenkins-installation:8080/exit

    http://jenkins-installation:8080/systemInfo
        
<a name="None"></a>
### workaround for svn connection problems

    sudo nano /etc/init.d/jenkins
        
look for the following lines
        
    # --user in daemon doesn't prepare environment variables like HOME, USER, LOGNAME or USERNAME,
    # so we let su do so for us now
    $SU -l $JENKINS_USER --shell=/bin/bash -c "$DAEMON $DAEMON_ARGS -- $JAVA $JAVA_ARGS -jar $JENKINS_WAR $JENKINS_ARGS" || return 2
            
the following should be inserted by you: `-Djsse.enableSNIExtension=false` so you get:
            
    $SU -l $JENKINS_USER --shell=/bin/bash -c "$DAEMON $DAEMON_ARGS -- $JAVA $JAVA_ARGS -jar -Djsse.enableSNIExtension=false $JENKINS_WAR $JENKINS_ARGS" || return 2
            
<a name="None"></a>
### fixing timezone-differences 

    sudo nano /etc/default/jenkins
            
insert the following there:
            
    JAVA_ARGS="-Dorg.apache.commons.jelly.tags.fmt.timeZone=Europe/Berlin"
            
<a name="javascript"></a>
## JavaScript WebdriverIO-Testrunner with Mocha[javascript]
            
<a name="None"></a>
### Plugins in Jenkins IDE required
    
* Email extension
* NodeJS
* JUnit

<a name="None"></a>
### Packages to install globally on jenkins server
* NodeJS
* npm

    sudo apt-get update
    sudo apt-get install nodejs
    sudo apt-get install npm

Unfortunately there is a conflict with another package from Ubuntu, therefor the NodeJS executable is named nodejs instead of node.
Keep that in mind as you are running software, or change this with:

    sudo ln -s "$(which nodejs)" /usr/bin/node
        
with npm installed install the following modules

    sudo npm install selenium-standalone@latest -g
    sudo selenium-standalone install

to start the selenium-server on server startup create a symbolic link in `/etc/init.d` and configure it to run
        
    sudo ln -s /usr/local/bin/selenium-standalone /etc/init.d/
    sudo update-rc.d selenium-standalone defaults

NOTE!!! This caused the following error in my vagrant:

    ==> default: Mounting shared folders...
        default: /vagrant => /Volumes/sebastian_TC/VM-Box-Versionen/Jenkins_Test
        Failed to mount folders in Linux guest. This is usually because
        the "vboxsf" file system is not available. Please verify that
        the guest additions are properly installed in the guest and
        can work properly. The command attempted was:
        
        mount -t vboxsf -o uid=`id -u www-data`,gid=`getent group www-data | cut -d: -f3`,dmode=777,fmode=777 vagrant /vagrant
        mount -t vboxsf -o uid=`id -u www-data`,gid=`id -g www-data`,dmode=777,fmode=777 vagrant /vagrant
        
        The error output from the last command was:
        
        stdin: is not a tty
        /sbin/mount.vboxsf: mounting failed with the error: No such device
        
        
on next startup selenium-server will be up automatically

    sudo npm install jasmine webdriverio wdio-jasmine-framework phantomjs -g
        
<a name="None"></a>
### fixing xunit bug in webdriverio (not fixed by: 2.Oktober 2015)

    sudo nano /var/lib/jenkins/tools/jenkins.plugins.nodejs.tools.NodeJSInstallation/Node-Default/lib/node_modules/webdriverio/lib/reporter/xunit.js
        
look for lines:
        
    self.write(tag('testcase', {
                name: test.title,
                disabled: test.pending,
                time: test.time / 1000,
                id: test.id,
                file: test.file,
                status: Object.getOwnPropertyNames(test.err).length === 0 ? 'passed' : 'failed',
                    
and insert `test.err &&` at status
 
    self.write(tag('testcase', {
                name: test.title,
                disabled: test.pending,
                time: test.time / 1000,
                id: test.id,
                file: test.file,
                status: test.err && Object.getOwnPropertyNames(test.err).length === 0 ? 'passed' : 'failed',
                    
<a name="None"></a>
### configure jenkins IDE

configure jenkins -> system configuration

  1. NodeJS installation
  Choose Installation `NodeJS 4.1.1`
  Global npm packages to install `webdriverio jasmine wdio-jasmine-framework phantomjs`
  
  2. Jenkins Location
  Jenkins URL e.g. http://192.168.56.103:8080/
  Email of systemadministrator 
  
  3. Extended E-mail Notification
  SMTP server 
  Default Subject `$PROJECT_NAME - Build # $BUILD_NUMBER - $BUILD_STATUS!`
  Default Content `${JELLY_SCRIPT,template="html"}`
  
<a name="None"></a>
### create build project

Enter name and choose "Free Style" project

<a name="None"></a>
### Source-Code-Managment
-> Subversion
    Repository URL 
    Credentials 
-> Buildumgebung
    Provide Node & npm bin/folder to PATH ...check...
    Installation ...choose the recently created...
-> Buildverfahren
    Shell ausführen
~~`npm install`~~ (dunno need because we installed everything globally before)
~~`selenium-standalone start &`~~ (selenium-server is already running at server-startup)
      `wdio tests/resources/wdio.conf.js`
    
<a name="None"></a>
### Post-Build-actions
-> publish JUNIT-results
    Testberichte in XML-Format `**/tests/results/*.xml`

-> Editable Email Notification
    Project recipient List  `$DEFAULT_RECIPIENTS`
    Project Reply-To List `$DEFAULT_REPLYTO`
    Content Type `Default Content Type`
    Default Subject `$DEFAULT_SUBJECT`
    Default Content `$DEFAULT_CONTENT`
    
    
<a name="phantomjs"></a>
## Testing with Mocha(Chai), WebdriverIO and PhantomJS[phantomjs]

<a name="None"></a>
### Jenkins configuration

NodeJS configuration

NodeJS Version 4.1.1

Global npm packages to install

    webdriverio mocha chai wdio-mocha-framework phantomjs
    
    if you want to use Jasmine
    
    jasmine wdio-jasmine-framework 
    
Shell command (if selenium is no installed on server)
    
    Selenium-standalone install
    
    sudo npm install selenium-standalone -g
    sudo selenium-standalone install
    
<a name="None"></a>
### Configure Build

Folder structure of repository
 
        tests
            resources
                wdio.conf.js
            scripts
                webdriverio
                    diomega.spec.js

so the shell command should look like this:
        
        wdio tests/resources/wdio.conf.js

<a name="None"></a>
### the respective config file `wdio.conf.js`

```javascript
exports.config = {
    
    host: '192.168.56.103',
    port: 4444,
    path: '/wd/hub',

    specs: [
        './tests/scripts/webdriverio/**/*.js',
        './tests/scripts/webdriverio/*.js'
    ],
    exclude: [
        // 'path/to/excluded/files'
    ],
    capabilities: [{
        browserName: 'phantomjs',
        'phantomjs.binary.path': '/usr/local/bin/phantomjs'
    }],
    logLevel: 'verbose',
    coloredLogs: true,
    screenshotPath: './errorShots/',
    baseUrl: 'http://the-internet.herokuapp.com',
    waitforTimeout: 20000,
    framework: 'mocha',
    reporter: 'xunit',
    reporterOptions: {
        outputDir: './tests/results/'
    },
    //onPrepare: function() {
        // do something
    //},
    before: function(done) {
        console.log('before...lets get started');
    },
    after: function(failures, pid) {
        console.log('after...stop everything');
    },
    //onComplete: function() {
        // do something
    //}
};
```

<a name="None"></a>
### Testing with ES6 generators (will reduce the callback-hell... ;))

The wdio test runner supports ES6 generators.
The following code therefor works as intended (with `baseUrl: 'http://the-internet.herokuapp.com'` set in wdio.config.js

```javascript
"use strict"
var assert = require('assert');

describe('checkboxes', function() {

    it('checkbox 2 should be enabled', function*() {
        yield browser.url('/checkboxes');
        yield browser.isSelected('#checkboxes input:last-Child').then(function(isSelected) {
            assert.equal(isSelected, true);
            /*expect(isSelected).toBe(true);*/
        });
    });

    it('checkbox 1 should be enabled after clicking on it', function*() {
        yield browser.url('/checkboxes');
        yield browser.isSelected('#checkboxes input:first-Child').then(function(isSelected) {
            assert.equal(isSelected, false);
        });
        yield browser.click('#checkboxes input:first-Child');
        yield browser.isSelected('#checkboxes input:first-Child').then(function(isSelected) {
            assert.equal(isSelected, true);
        });
    });

});

```

<a name="None"></a>
### the results will be published in `/tests/results/*.xml`

see `wdio.conf.js`
so post-build-actions will be

publish JUnit results

`**/tests/results/*.xml`


#### problems with phantomjs

if you try to e.g. .click() an element and the following error ocurrs
 `Problem: Element is not currently visible and may not be manipulated`
this is a webdriverIO bug, that meand that the element you tried to click is currently not visible in phantomjs, because of the default window size
   
in this case try a higher window size before launching your tests
e.g.:

```javascript
describe('a click event', function () {
    console.log('test....starts now....');
    it('should open a new page', function(done) {
        browser
            .setViewportSize({
                width: 1024,
                height: 768
            })
            .windowHandleSize().then(function(size) {
                console.log('...this is the size',size);
            })
            .click('.ac-gn-link-mac').pause(2000).getTitle().then(function (title) {
                console.log(title);
                expect(title).toEqual('Mac – Apple (DE)');
                done();
            });
    })
});
```

<a name="php"></a>
# PHP[php]

<a name="phpant"></a>
## Ant Version[phpant]

<a name="None"></a>
### install phpunit

<a name="None"></a>
### install Ant if not done by Jenkins

    sudo apt-get install ant
        
<a name="None"></a>
### otherwise go to Jenkins and configure Ant-Installation        
        
configure Jenkins -> configure system -> Ant -> Ant installations -> install automatically

configure jenkins -> configure plugins -> install Ant plugin
                

            
#### editable email notifications

install plugin to send email notifications

---

<a name="None"></a>
# Plugins for php

<a name="phpunit"></a>
## PHPunit[phpunit]

<a name="None"></a>
### installation on server
```sh

    curl -sS https://getcomposer.org/installer | php
    
    sudo mv composer.phar /usr/local/bin/composer
    
    composer global require "phpunit/phpunit=5.0.*"
    
    //sudo nano ~/.bashrc
    
    //export PATH=$PATH:~/.composer/vendor/bin/
```


#### upgrade php to 5.6
    
```sh
    sudo apt-get -y update
    
    add-apt-repository ppa:ondrej/php5-5.6
    
    sudo apt-get -y update
    
    sudo apt-get -y install php5
    
    php -v //to verify the version
```

#### enable php-code-coverage like 'clover'

Clover provides the metrics you need to better balance the effort between writing code that does stuff, and code that tests stuff.

if not, install xdebug for phpunit
        
    sudo pecl install xdebug
        
and add this to php.ini

    zend_extension=/usr/lib/php5/20121212/xdebug.so
    
    sudo service apache2 restart
        
if you encounter **The Zend Engine API version 220131226 which is installed, is newer.** then do:

go to ([http://xdebug.org/wizard.php](http://xdebug.org/wizard.php))
paste in `php -i` output and let it analyse this
for me it produced these instructions

Download
 
    wget http://xdebug.org/files/xdebug-2.4.0beta1.tgz

Unpack the downloaded file with 

    tar -xvzf xdebug-2.4.0beta1.tgz
run:    
     
    cd xdebug-2.4.0beta1
    phpize (See the FAQ if you don't have phpize.
        
As part of its output it should show:
        
    Configuring for:
    ...
    Zend Module Api No:      20131226
    Zend Extension Api No:   220131226

If it does not, you are using the wrong phpize. Please follow this FAQ entry and skip the next step.
        
Run: 
        
    ./configure
    make
    cp modules/xdebug.so /usr/lib/php5/20131226
    sudo nano /etc/php5/cli/php.ini
        
and add the line

    zend_extension = /usr/lib/php5/20131226/xdebug.so

<a name="None"></a>
### jenkins plugin to visualize

[XUnit](http://wiki.jenkins-ci.org/display/JENKINS/xUnit+Plugin)

<a name="None"></a>
### ant target in `build.xml`

```xml        
    <!--perform phpunit tests with coverage report-->

    <target name="phpunit"
            unless="phpunit.done"
            depends="prepare"
            description="Run unit tests with PHPUnit">
        <exec executable="${phpunit}" resultproperty="result.phpunit" taskname="phpunit">
            <arg value="--configuration"/>
            <arg path="${basedir}/phpunit.xml"/>
        </exec>

        <property name="phpunit.done" value="true"/>
    </target>

    <!--perform phpunit tests without coverage report-->

    <target name="phpunit-no-coverage"
            unless="phpunit.done"
            depends="prepare"
            description="Run unit tests with PHPUnit (without generating code coverage reports)">
        <exec executable="${phpunit}" failonerror="true" taskname="phpunit">
            <arg value="--configuration"/>
            <arg path="${basedir}/phpunit.xml"/>
            <arg value="--no-coverage"/>
        </exec>

        <property name="phpunit.done" value="true"/>
    </target>
```

<a name="None"></a>
### configuration file (`/phpunit.xml`)

```xml
    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit
            backupGlobals="false"
            backupStaticAttributes="false"
            strict="true"
            verbose="true">
        <testsuites>
            <testsuite name="ProjectName">
                <directory suffix="Test.php">tests/</directory>
                <directory suffix="Test.php">tests/</directory>
            </testsuite>
        </testsuites>

        <logging>
            <log type="coverage-html" target="coverage"/>
            <log type="coverage-clover" target="reports/clover.xml"/>
            <log type="coverage-crap4j" target="reports/crap4j.xml"/>
            <log type="junit" target="reports/junit.xml" logIncompleteSkipped="false"/>
        </logging>

        <filter>
            <whitelist addUncoveredFilesFromWhitelist="true">
                <directory suffix=".php">src</directory>
                <exclude>
                    <file>src/bootstrap.php</file>
                </exclude>
            </whitelist>
        </filter>
    </phpunit>
```

<a name="None"></a>
### jenkins post-build job configuration

Publish JUnit results: `**/logs/junit.xml`
Publish Clover results:  **Clover XML Location** `build/clover.xml`



---


<a name="phpcs"></a>
## PHP_CodeSniffer[phpcs]

<a name="None"></a>
### installation on server

    sudo pear install PHP_CodeSniffer
        
    //command: phpcs
        
<a name="None"></a>
### check which coding standards are installed

    phpcs -i
        
    //The installed coding standards are Squiz, PSR2, PSR1, PHPCS, Zend, PEAR and MySource
        
<a name="None"></a>
### jenkins plugin to visualize

[Checkstyle](http://wiki.jenkins-ci.org/display/JENKINS/Checkstyle+Plugin)

<a name="None"></a>
### ant target in `build.xml`
```xml
    <!--find coding standard violations using CodeSniffer (command line version)-->

    <target name="phpcs"
            unless="phpcs.done"
            description="Find coding standard violations using PHP_CodeSniffer and print human readable output. Intended for usage on the command line before committing.">
        <exec executable="${phpcs}" taskname="phpcs">
            <arg value="--standard=PSR2" />
            <arg value="&#45;&#45;standard=PHPCS" />
            <arg value="&#45;&#45;standard=Zend" />
            <arg value="&#45;&#45;standard=PEAR" />
            <arg value="&#45;&#45;standard=MySource" />
            <arg value="&#45;&#45;standard=PSR1" />
            <arg value="&#45;&#45;standard=Squiz" />
            <arg value="--extensions=php" />
            <arg value="--ignore=autoload.php" />
            <arg path="${basedir}/src" />
            <arg path="${basedir}/tests" />
        </exec>

        <property name="phpcs.done" value="true"/>
    </target>

    <!--find coding standard violations usind CodeSniffer (jenkins)-->

    <target name="phpcs-ci"
            unless="phpcs.done"
            depends="prepare"
            description="Find coding standard violations using PHP_CodeSniffer and log result in XML format. Intended for usage within a continuous integration environment.">
        <exec executable="${phpcs}" output="/dev/null" taskname="phpcs">
            <arg value="--report=checkstyle" />
            <arg value="--report-file=${basedir}/build/logs/checkstyle.xml" />
            <arg value="--standard=PSR2" />
            <arg value="&#45;&#45;standard=PHPCS" />
            <arg value="&#45;&#45;standard=Zend" />
            <arg value="&#45;&#45;standard=PEAR" />
            <arg value="&#45;&#45;standard=MySource" />
            <arg value="&#45;&#45;standard=PSR1" />
            <arg value="&#45;&#45;standard=Squiz" />-->
            <arg value="--extensions=php" />
            <arg value="--ignore=autoload.php" />
            <arg path="${basedir}/src" />
            <arg path="${basedir}/tests" />
        </exec>

        <property name="phpcs.done" value="true"/>
    </target>
```

<a name="None"></a>
### configuration file (under `build/ruleset.xml`)
        
        <?xml version="1.0"?>
        <ruleset name="name-of-your-coding-standard">
            <description>Description of your coding standard</description>
        
            <rule ref="Generic.PHP.DisallowShortOpenTag"/>
            <!-- ... -->
        </ruleset>
        
<a name="None"></a>
### jenkins post-build job configuration

Checkstyle: `**/build/logs/checkstyle.xml`

---
        
<a name="phploc"></a>
## PHP_loc - lines of code[phploc]   
   
<a name="None"></a>
### installation on server   
  
    composer global require 'phploc/phploc=*'

    //command: phploc
        
<a name="None"></a>
### jenkins plugin to visualize

[Plot](http://wiki.jenkins-ci.org/display/JENKINS/Plot+Plugin)

<a name="None"></a>
### ant target in `build.xml`
```sh
    <target name="phploc"
            unless="phploc.done"
            description="Measure project size using PHPLOC and print human readable output. Intended for usage on the command line.">
        <exec executable="${phploc}" taskname="phploc">
            <arg value="--count-tests" />
            <arg path="${basedir}/src" />
            <arg path="${basedir}/tests" />
        </exec>

        <property name="phploc.done" value="true"/>
    </target>

    <!--count lines of code (jenkins)-->

    <target name="phploc-ci"
            unless="phploc.done"
            depends="prepare"
            description="Measure project size using PHPLOC and log result in CSV and XML format. Intended for usage within a continuous integration environment.">
        <exec executable="${phploc}" taskname="phploc">
            <arg value="--count-tests" />
            <arg value="--log-csv" />
            <arg path="${basedir}/build/logs/phploc.csv" />
            <arg value="--log-xml" />
            <arg path="${basedir}/build/logs/phploc.xml" />
            <arg path="${basedir}/src" />
            <arg path="${basedir}/tests" />
        </exec>

        <property name="phploc.done" value="true"/>
    </target>
```

<a name="None"></a>
### jenkins post-build job configuration

Plot:
 
**data series file** `build/logs/phploc.csv`

**load data from csv**

---
        
<a name="phpdepend"></a>
## PHP_Depend[phpdepend]

<a name="None"></a>
### installation on server   

    composer global require 'pdepend/pdepend=*'
        
    //command: pdepend
        
<a name="None"></a>
### jenkins plugin to visualize

[JDepend](http://wiki.jenkins-ci.org/display/JENKINS/JDepend+Plugin)

<a name="None"></a>
### ant target in `build.xml`      
```sh
    <!--performs static code analysis on a given source base, the sum of some statements or code fragments found in the analyzed source-->

    <target name="pdepend"
            unless="pdepend.done"
            depends="prepare"
            description="Calculate software metrics using PHP_Depend and log result in XML format. Intended for usage within a continuous integration environment.">
        <exec executable="${pdepend}" taskname="pdepend">
            <arg value="--summary-xml=${basedir}/build/logs/jdepend.xml" />
            <arg value="--jdepend-xml=${basedir}/build/logs/jdepend.xml" />
            <arg value="--jdepend-chart=${basedir}/build/pdepend/dependencies.svg" />
            <arg value="--overview-pyramid=${basedir}/build/pdepend/overview-pyramid.svg" />
            <arg path="${basedir}/src" />
        </exec>

        <property name="pdepend.done" value="true"/>
    </target>
```

<a name="None"></a>
### jenkins post-build job configuration

Report JDepend

**Pre-generated JDepend File**: `build/logs/jdepend.xml`

<a name="None"></a>
### integrate Jdependend SVG reports
        
in project description insert 
```html
    <img type="image/svg+xml" height="300" src="ws/build/pdepend/overview-pyramid.svg" width="500"></img>
    <img type="image/svg+xml" height="300" src="ws/build/pdepend/   dependencies.svg" width="500"></img>
```

Jenkins is probably configured to text, therefor: 

configure jenkins -> global security -> change markup to html
        
---
        
<a name="phpmd"></a>
## PHPMD - PHP Mess Detector[phpmd]

[Clean Code Rules](http://phpmd.org/rules/cleancode.html)
[Code Size Rules](http://phpmd.org/rules/codesize.html)
[Controversial Rules](http://phpmd.org/rules/controversial.html)
[Design Rules](http://phpmd.org/rules/design.html)
[Naming Rules](http://phpmd.org/rules/naming.html)
[Unused Code Rules](http://phpmd.org/rules/unusedcode.html)

<a name="None"></a>
### installation on server  

    composer global require 'phpmd/phpmd=*'
                
    //command: phpmd
   
<a name="None"></a>
### jenkins plugin to visualize

[PMD](http://wiki.jenkins-ci.org/display/JENKINS/PMD+Plugin)

<a name="None"></a>
### ant target in `build.xml`
```xml        
    <!--perform mess detection (command line version)-->

    <target name="phpmd"
            unless="phpmd.done"
            description="Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing.">
        <exec executable="${phpmd}" taskname="phpmd">
            <arg path="${basedir}/src" />
            <arg value="text" />
            <arg path="${basedir}/phpmd.xml" />
        </exec>

        <property name="phpmd.done" value="true"/>
    </target>

    <!--perform mess detection (jenkins)-->

    <target name="phpmd-ci"
            unless="phpmd.done"
            depends="prepare"
            description="Perform project mess detection using PHPMD and log result in XML format. Intended for usage within a continuous integration environment.">
        <exec executable="${phpmd}" taskname="phpmd">
            <arg path="${basedir}/src" />
            <arg value="xml" />
            <arg path="${basedir}/phpmd.xml" />
            <arg value="--reportfile" />
            <arg path="${basedir}/build/logs/pmd.xml" />
        </exec>

        <property name="phpmd.done" value="true"/>
    </target>
```

<a name="None"></a>
### configuration file (under `build/phpmd.xml`)
```xml
    <ruleset name="names-of-your-coding-standard"
             xmlns="http://pmd.sf.net/ruleset/1.0.0"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0
                          http://pmd.sf.net/ruleset_xml_schema.xsd"
             xsi:noNamespaceSchemaLocation="http://pmd.sf.net/ruleset_xml_schema.xsd">
        <description>Description of your coding standard</description>

        <!--The Code Size Ruleset contains a collection of rules that find code size related problems.-->
        <rule ref="rulesets/codesize.xml/CyclomaticComplexity" />
        <rule ref="rulesets/codesize.xml/ExcessiveMethodLength" />
        <rule ref="rulesets/codesize.xml/ExcessiveClassLength" />
        <rule ref="rulesets/codesize.xml/ExcessiveParameterList" />
        <rule ref="rulesets/codesize.xml/ExcessivePublicCount" />
        <rule ref="rulesets/codesize.xml/TooManyFields" />
        <rule ref="rulesets/codesize.xml/TooManyMethods" />
        <rule ref="rulesets/codesize.xml/TooManyPublicMethods" />
        <rule ref="rulesets/codesize.xml/ExcessiveClassComplexity" />

        <!--The Unused Code Ruleset contains a collection of rules that find unused code.-->
        <rule ref="rulesets/unusedcode.xml/UnusedPrivateField" />
        <rule ref="rulesets/unusedcode.xml/UnusedLocalVariable" />
        <rule ref="rulesets/unusedcode.xml/UnusedPrivateMethod" />
        <rule ref="rulesets/unusedcode.xml/UnusedFormalParameter" />

        <!--The Clean Code ruleset contains rules that enforce a clean code base. This includes rules from SOLID and object calisthenics.-->
        <rule ref="rulesets/cleancode.xml/BooleanArgumentFlag" />
        <rule ref="rulesets/cleancode.xml/ElseExpression" />
        <rule ref="rulesets/cleancode.xml/StaticAccess" />

        <!--The Code Size Ruleset contains a collection of rules that find software design related problems.-->
        <rule ref="rulesets/design.xml/ExitExpression" />
        <rule ref="rulesets/design.xml/EvalExpression" />
        <rule ref="rulesets/design.xml/GotoStatement" />
        <rule ref="rulesets/design.xml/NumberOfChildren" />
        <rule ref="rulesets/design.xml/DepthOfInheritance" />
        <rule ref="rulesets/design.xml/CouplingBetweenObjects" />

        <!--The Naming Ruleset contains a collection of rules about names - too long, too short, and so forth.-->
        <rule ref="rulesets/naming.xml/ShortVariable" />
        <rule ref="rulesets/naming.xml/LongVariable" />
        <rule ref="rulesets/naming.xml/ShortMethodName" />
        <rule ref="rulesets/naming.xml/ConstructorWithNameAsEnclosingClass" />
        <rule ref="rulesets/naming.xml/ConstantNamingConventions" />
        <rule ref="rulesets/naming.xml/BooleanGetMethodName" />

        <!--This ruleset contains a collection of controversial rules.-->
        <rule ref="rulesets/controversial.xml/Superglobals" />
        <rule ref="rulesets/controversial.xml/CamelCaseClassName" />
        <rule ref="rulesets/controversial.xml/CamelCasePropertyName" />
        <rule ref="rulesets/controversial.xml/CamelCaseMethodName" />
        <rule ref="rulesets/controversial.xml/CamelCaseParameterName" />
        <rule ref="rulesets/controversial.xml/CamelCaseVariableName" />

    </ruleset>
```

<a name="None"></a>
### jenkins post-build job configuration

Report PMD anylsis

**filename PMD results**: `build/logs/pmd.xml`
        
---
        
<a name="phpcpd"></a>
## PHPCPD - Copy-Paste-Detector[phpcpd]

<a name="None"></a>
### installation on server  
        
    composer global require "sebastian/phpcpd=*"
        
    //command: phpcpd
        
<a name="None"></a>
### jenkins plugin to visualize

[Duplicate Code Scanner Plug-in](http://wiki.jenkins-ci.org/x/X4IuAg)

<a name="None"></a>
### ant target in `build.xml`
```xml        
    <!--find duplicate code (command line version)-->

    <target name="phpcpd"
            unless="phpcpd.done"
            description="Find duplicate code using PHPCPD and print human readable output. Intended for usage on the command line before committing.">
        <exec executable="${phpcpd}" taskname="phpcpd">
            <arg path="${basedir}/src" />
        </exec>

        <property name="phpcpd.done" value="true"/>
    </target>

    <!--find duplicate code (jenkins)-->

    <target name="phpcpd-ci"
            unless="phpcpd.done"
            depends="prepare"
            description="Find duplicate code using PHPCPD and log result in XML format. Intended for usage within a continuous integration environment.">
        <exec executable="${phpcpd}" taskname="phpcpd">
            <arg value="--log-pmd" />
            <arg path="${basedir}/build/logs/pmd-cpd.xml" />
            <arg path="${basedir}/src" />
        </exec>

        <property name="phpcpd.done" value="true"/>
    </target>
```

<a name="None"></a>
### jenkins post-build job configuration

Report Duplicate Code Scanner results

**filename**: `**/build/logs/pmd-cpd.xml`
        
---
        
<a name="phpdox"></a>
## PHP_dox[phpdox]

<a name="None"></a>
### installation on server  
        
    wget https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-0.8.0.phar
        
    chmod +x phpdox-0.8.0.phar
        
    sudo mv phpdox-0.8.0.phar /usr/bin/phpdox
        
    //if not installed:
    sudo apt-get install php5-xsl
        
<a name="None"></a>
### jenkins plugin to visualize

[HTML publisher](http://wiki.jenkins-ci.org/display/JENKINS/HTML+Publisher+Plugin)

<a name="None"></a>
### ant target in `build.xml`
```xml        
    <target name="phpdox"
            unless="phpdox.done"
            depends="phploc-ci,phpcs-ci,phpmd-ci"
            description="Generate project documentation using phpDox">
        <exec executable="${phpdox}" dir="${basedir}" taskname="phpdox"/>

        <property name="phpdox.done" value="true"/>
    </target>
```

<a name="None"></a>
### configuration file (under `build/phpdox.xml`)
```xml
    <?xml version="1.0"?>
    <phpdox xmlns='http://xml.phpdox.net/config'>
        <project name="name-of-project" source="${basedir}/src" workdir="${basedir}/phpdox">
            <collector publiconly="false">
                <include mask="*.php" />
            </collector>

            <generator output="${basedir}/reports/api">
                <enrich base="${basedir}/reports">
                    <source type="build" />
                    <source type="checkstyle" />
                    <source type="pmd" />
                    <source type="phploc" />
                </enrich>
                <build engine="html" enabled="true">
                    <file extension="html" />
                </build>
            </generator>
        </project>
    </phpdox>
```

<a name="None"></a>
### jenkins post-build job configuration

Publish HTML reports

**directory**: `build/api`
**index page**: `index.html`
**report title**: `HTML Report`
        
---
        
<a name="None"></a>
## PHP_CodeBrowser (not in use!)
        
    composer global require "mayflower/php-codebrowser=~1.1"
        
    //command: phpcb
        
---

<a name="phpmetrics"></a>
## PHPMetrics[phpmetrics]

<a name="None"></a>
### installation on server  

    composer global require "halleck45/phpmetrics"
        
    //command: phpmetrics
        
<a name="None"></a>
### jenkins plugin to visualize

- for phpmetrics.xml option:
   [Plot](http://wiki.jenkins-ci.org/display/JENKINS/Plot+Plugin)

   **data series file** `build/logs/phpmetrics.xml`

   **load data from xml using xpath**
   **xpath result type** number

- for violations.xml option:
   [violation](http://wiki.jenkins-ci.org/display/JENKINS/Violations)

   under pmd `build/logs/violations.xml`

- for phpmetrics.html option:
   [sidebar-link](http://wiki.jenkins-ci.org/display/JENKINS/Sidebar-Link+Plugin)

<a name="None"></a>
### ant target in `build.xml`

```xml        
    <!--generate documentation-->

    <target name="phpmetrics"
            unless="phpmetrics.done"
            description="generate phpmetrics reports">
        <exec executable="${phpmetrics}" taskname="phpmetrics">
            <arg value="--report-xml=${basedir}/build/logs/phpmetrics.xml"/>
            <arg value="--violations-xml=${basedir}/build/logs/violations.xml"/>
            <arg value="--report-html=${basedir}/build/logs/quality.html"/>
            <arg path="${basedir}/src"/>
        </exec>

        <property name="phpmetrics.done" value="true"/>
    </target>
```

<a name="None"></a>
### jenkins post-build job configuration
        
###### for phpmetrics.xml option:
   Plot

   **data series file** `build/logs/phpmetrics.xml`

   **load data from xml using xpath**
   **xpath result type** number

###### for violations.xml option:
   Violation

   under pmd `build/logs/violations.xml`

###### for phpmetrics.html option:
   sidebar-links(right under description) 
   **link url** `http://192.168.56.103:8080/job/PHPUnit/ws/build/logs/quality.html`
   **link text** `PhpMetrics report`
   **link icon** `document.gif`
        
to understand the results have a look at: ([how-to-read-report](http://www.phpmetrics.org/documentation/how-to-read-report.html))


<a name="phpcompleteant"></a>
## complete working solution `build.xml`[phpcompleteant]

```xml
    <project name="name-of-project" default="full-build" basedir=".">

        <!--location of executables-->
        <property name="pdepend" value="/home/vagrant/.composer/vendor/bin/pdepend"/>
        <property name="phpcpd" value="/home/vagrant/.composer/vendor/bin/phpcpd"/>
        <property name="phploc" value="/home/vagrant/.composer/vendor/bin/phploc"/>
        <property name="phpunit" value="/home/vagrant/.composer/vendor/bin/phpunit"/>
        <property name="phpmd" value="/home/vagrant/.composer/vendor/bin/phpmd"/>
        <property name="phpdox" value="/usr/bin/phpdox"/>
        <property name="phpcs" value="/usr/bin/phpcs"/>
        <property name="phpmetrics" value="/home/vagrant/.composer/vendor/bin/phpmetrics"/>

        <target name="full-build"
                depends="prepare, static-analysis, phpunit,phpdox,phpmetrics, -check-failure"
                description="Performs static analysis, runs tests and generates project dpcumentation">
        </target>

        <target name="full-build-parallel"
                depends="prepare,static-analysis-parallel,phpunit,phpdox,phpmetrics, -check-failure"
                description="Performs static analysis (executing the tools in parallel), runs the tests, and generates project documentation">
        </target>

        <target name="quick-build"
                depends="prepare, lint, phpunit-no-coverage"
                description="Performs a lint check and runs tests without generating codce coverage reports">
        </target>

        <target name="static-analysis"
                depends="lint, phploc-ci, pdepend, phpmd-ci, phpcs-ci, phpcpd-ci"
                description="Performs static analysis">
        </target>

        <target name="static-analysis-parallel"
                description="Performs static analysis (executing the tools in parallel)">
            <parallel threadCount="2">
                <sequential>
                    <antcall target="pdepend"/>
                    <antcall target="phpmd-ci"/>
                </sequential>
                <antcall target="lint"/>
                <antcall target="phpcpd-ci"/>
                <antcall target="phpcs-ci"/>
                <antcall target="phploc-ci"/>
            </parallel>
        </target>

        <!--remove directories of previous builds-->

        <target name="clean"
                unless="clean.done"
                description="Cleanup build artifacts">
            <delete dir="${basedir}/build/api"/>
            <delete dir="${basedir}/build/coverage"/>
            <delete dir="${basedir}/build/logs"/>
            <delete dir="${basedir}/build/pdepend"/>
            <delete dir="${basedir}/build/phpdox"/>
            <property name="clean.done" value="true"/>
        </target>

        <!--create directories for logs, coverage etc.-->

        <target name="prepare"
                unless="prepare.done"
                depends="clean"
                description="Prepare for build">
            <mkdir dir="${basedir}/build/api"/>
            <mkdir dir="${basedir}/build/coverage"/>
            <mkdir dir="${basedir}/build/logs"/>
            <mkdir dir="${basedir}/build/pdepend"/>
            <mkdir dir="${basedir}/build/phpdox"/>
            <property name="prepare.done" value="true"/>
        </target>

        <!--perform syntax check of source-code with php -l-->

        <target name="lint"
                unless="lint.done"
                description="Perform syntax check of sourcecode files">
            <apply executable="php" taskname="lint">
                <arg value="-l" />

                <fileset dir="${basedir}/build/src">
                    <include name="**/*.php" />
                    <modified />
                </fileset>

                <fileset dir="${basedir}/build/tests">
                    <include name="**/*.php" />
                    <modified />
                </fileset>
            </apply>

            <property name="lint.done" value="true"/>
        </target>

        <!--count lines of code (command line usage)-->

        <target name="phploc"
                unless="phploc.done"
                description="Measure project size using PHPLOC and print human readable output. Intended for usage on the command line.">
            <exec executable="${phploc}" taskname="phploc">
                <arg value="--count-tests" />
                <arg path="${basedir}/src" />
                <arg path="${basedir}/tests" />
            </exec>

            <property name="phploc.done" value="true"/>
        </target>

        <!--count lines of code (jenkins)-->

        <target name="phploc-ci"
                unless="phploc.done"
                depends="prepare"
                description="Measure project size using PHPLOC and log result in CSV and XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${phploc}" taskname="phploc">
                <arg value="--count-tests" />
                <arg value="--log-csv" />
                <arg path="${basedir}/build/logs/phploc.csv" />
                <arg value="--log-xml" />
                <arg path="${basedir}/build/logs/phploc.xml" />
                <arg path="${basedir}/src" />
                <arg path="${basedir}/tests" />
            </exec>

            <property name="phploc.done" value="true"/>
        </target>

        <!--performs static code analysis on a given source base, the sum of some statements or code fragments found in the analyzed source-->

        <target name="pdepend"
                unless="pdepend.done"
                depends="prepare"
                description="Calculate software metrics using PHP_Depend and log result in XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${pdepend}" taskname="pdepend">
                <arg value="--summary-xml=${basedir}/build/logs/jdepend.xml" />
                <arg value="--jdepend-xml=${basedir}/build/logs/jdepend.xml" />
                <arg value="--jdepend-chart=${basedir}/build/pdepend/dependencies.svg" />
                <arg value="--overview-pyramid=${basedir}/build/pdepend/overview-pyramid.svg" />
                <arg path="${basedir}/src" />
            </exec>

            <property name="pdepend.done" value="true"/>
        </target>

        <!--perform mess detection (command line version)-->

        <target name="phpmd"
                unless="phpmd.done"
                description="Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpmd}" taskname="phpmd">
                <arg path="${basedir}/src" />
                <arg value="text" />
                <arg path="${basedir}/phpmd.xml" />
            </exec>

            <property name="phpmd.done" value="true"/>
        </target>

        <!--perform mess detection (jenkins)-->

        <target name="phpmd-ci"
                unless="phpmd.done"
                depends="prepare"
                description="Perform project mess detection using PHPMD and log result in XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${phpmd}" taskname="phpmd">
                <arg path="${basedir}/src" />
                <arg value="xml" />
                <arg path="${basedir}/phpmd.xml" />
                <arg value="--reportfile" />
                <arg path="${basedir}/build/logs/pmd.xml" />
            </exec>

            <property name="phpmd.done" value="true"/>
        </target>

        <!--find coding standard violations using CodeSniffer (command line version)-->

        <target name="phpcs"
                unless="phpcs.done"
                description="Find coding standard violations using PHP_CodeSniffer and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpcs}" taskname="phpcs">
                <arg value="--standard=PSR2" />
    <!--            <arg value="&#45;&#45;standard=PHPCS" />
                <arg value="&#45;&#45;standard=Zend" />
                <arg value="&#45;&#45;standard=PEAR" />
                <arg value="&#45;&#45;standard=MySource" />
                <arg value="&#45;&#45;standard=PSR1" />
                <arg value="&#45;&#45;standard=Squiz" />-->
                <arg value="--extensions=php" />
                <arg value="--ignore=autoload.php" />
                <arg path="${basedir}/src" />
                <arg path="${basedir}/tests" />
            </exec>

            <property name="phpcs.done" value="true"/>
        </target>

        <!--find coding standard violations usind CodeSniffer (jenkins)-->

        <target name="phpcs-ci"
                unless="phpcs.done"
                depends="prepare"
                description="Find coding standard violations using PHP_CodeSniffer and log result in XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${phpcs}" output="/dev/null" taskname="phpcs">
                <arg value="--report=checkstyle" />
                <arg value="--report-file=${basedir}/build/logs/checkstyle.xml" />
                <arg value="--standard=PSR2" />
    <!--            <arg value="&#45;&#45;standard=PHPCS" />
                <arg value="&#45;&#45;standard=Zend" />
                <arg value="&#45;&#45;standard=PEAR" />
                <arg value="&#45;&#45;standard=MySource" />
                <arg value="&#45;&#45;standard=PSR1" />
                <arg value="&#45;&#45;standard=Squiz" />-->
                <arg value="--extensions=php" />
                <arg value="--ignore=autoload.php" />
                <arg path="${basedir}/src" />
                <arg path="${basedir}/tests" />
            </exec>

            <property name="phpcs.done" value="true"/>
        </target>

        <!--find duplicate code (command line version)-->

        <target name="phpcpd"
                unless="phpcpd.done"
                description="Find duplicate code using PHPCPD and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpcpd}" taskname="phpcpd">
                <arg path="${basedir}/src" />
            </exec>

            <property name="phpcpd.done" value="true"/>
        </target>

        <!--find duplicate code (jenkins)-->

        <target name="phpcpd-ci"
                unless="phpcpd.done"
                depends="prepare"
                description="Find duplicate code using PHPCPD and log result in XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${phpcpd}" taskname="phpcpd">
                <arg value="--log-pmd" />
                <arg path="${basedir}/build/logs/pmd-cpd.xml" />
                <arg path="${basedir}/src" />
            </exec>

            <property name="phpcpd.done" value="true"/>
        </target>


        <!--perform phpunit tests with coverage report-->

        <target name="phpunit"
                unless="phpunit.done"
                depends="prepare"
                description="Run unit tests with PHPUnit">
            <exec executable="${phpunit}" resultproperty="result.phpunit" taskname="phpunit">
                <arg value="--configuration"/>
                <arg path="${basedir}/phpunit.xml"/>
            </exec>

            <property name="phpunit.done" value="true"/>
        </target>

        <!--perform phpunit tests without coverage report-->

        <target name="phpunit-no-coverage"
                unless="phpunit.done"
                depends="prepare"
                description="Run unit tests with PHPUnit (without generating code coverage reports)">
            <exec executable="${phpunit}" failonerror="true" taskname="phpunit">
                <arg value="--configuration"/>
                <arg path="${basedir}/phpunit.xml"/>
                <arg value="--no-coverage"/>
            </exec>

            <property name="phpunit.done" value="true"/>
        </target>

        <!--generate documentation-->

        <target name="phpmetrics"
                unless="phpmetrics.done"
                description="generate phpmetrics reports">
            <exec executable="${phpmetrics}" taskname="phpmetrics">
                <arg value="--report-xml=${basedir}/build/logs/phpmetrics.xml"/>
                <arg value="--violations-xml=${basedir}/build/logs/violations.xml"/>
                <arg value="--report-html=${basedir}/build/logs/quality.html"/>
                <arg path="${basedir}/src"/>
            </exec>

            <property name="phpmetrics.done" value="true"/>
        </target>

        <target name="phpdox"
                unless="phpdox.done"
                depends="phploc-ci,phpcs-ci,phpmd-ci"
                description="Generate project documentation using phpDox">
            <exec executable="${phpdox}" dir="${basedir}" taskname="phpdox"/>

            <property name="phpdox.done" value="true"/>
        </target>

        <target name="-check-failure">
            <fail message="PHPUnit did not finish successfully">
                <condition>
                    <not>
                        <equals arg1="${result.phpunit}" arg2="0"/>
                    </not>
                </condition>
            </fail>
        </target>
    </project>
```

<a name="phpcompletegradle"></a>
## Gradle Version[phpcompletegradle]
```text
    ext {
    sourceDir = 'src'
    testDir = 'tests'
    reportDir = 'build/reports'
    apiDir = 'build/reports/api'
    }

    task prepare(type: Delete) {
        delete reportDir
        delete apiDir
        doLast {
            file(reportDir).mkdirs()
            file(apiDir).mkdirs()
        }
    }

    def vendorBinDir = "/home/vagrant/.composer/vendor/bin"
    def usrVendorBinDir = "/usr/bin"
    def cmdTemplate = { cmd -> vendorBinDir + '/' + cmd}
    def usrCmdTemplate = { cmd -> usrVendorBinDir + '/' + cmd}

    task phploc(type: Exec, description: 'Measure project size using PHPLOC') {
        executable cmdTemplate(name)
            args "--count-tests", "--log-csv", "./build/reports/phploc.csv", "--log-xml", "./build/reports/phploc.xml", sourceDir, testDir
    }

    task pdepend(type: Exec, description: 'Calculate software metrics using PHPDepend') {
        executable cmdTemplate(name)
            args "--summary-xml=./build/reports/jdepend-summary.xml", "--jdepend-xml=./build/reports/jdepend.xml", "--jdepend-chart=./build/reports/pdepend-dependencies.svg", "--overview-pyramid=./build/reports/pdepend-overview-pyramid.svg", sourceDir
    }

    task phpmd (type : Exec, description : "Perform project mess detection using PHPMD, and Creating a log file for the Continuous Integration Server"){
        executable cmdTemplate(name)
            args sourceDir, "xml", "./build/phpmd.xml", "--reportfile", "./build/reports/phpmd.xml"
    }

    task phpcpd (type : Exec, description : "Find duplicate code using PHPCPD and log result in XML format."){
        executable cmdTemplate(name)
            args "--log-pmd", "build/reports/pmd-cpd.xml", sourceDir
    }

    task phpunit (type : Exec, description : "Run unit tests with PHPUnit"){
        executable cmdTemplate(name)
            args "-c", "build/phpunit.xml"
    }

    task phpcs (type : Exec, description : "Find Coding Standard Violations using PHP_CodeSniffer"){
        executable usrCmdTemplate(name)
            args "--report=checkstyle", "--report-file=./build/reports/checkstyle.xml", "--standard=PSR2", "--extensions=php", "--ignore=autoload.php", sourceDir, testDir
    }

    task phpdox (type : Exec, description : "Generating Docs enriched with pmd, checkstyle, phpcs,phpunit,phploc"){
        executable usrCmdTemplate(name)
            args "-f", "build/phpdox.xml"
    }

    task phpmetrics (type : Exec, description : "Geenrating PHPMetrics"){
        executable cmdTemplate(name)
            args "--report-xml=./build/reports/phpmetrics.xml", "--violations-xml=./build/reports/violations.xml", "--report-html=./build/reports/quality.html", sourceDir
    }

    task fullbuild (dependsOn: [prepare, phploc, pdepend, phpmd, phpcpd, phpunit, phpcs, phpdox, phpmetrics])
    phploc.mustRunAfter prepare
    pdepend.mustRunAfter prepare
    phpmd.mustRunAfter prepare
    phpcpd.mustRunAfter prepare
    phpunit.mustRunAfter prepare
    phpcs.mustRunAfter prepare
    phpdox.mustRunAfter prepare
    phpmetrics.mustRunAfter prepare    
```

        
<a name="java"></a>
# Java[java]

<a name="javaant"></a>
## Ant Version[javaant]

<a name="None"></a>
## download following .jar

[junit.jar](http://bit.ly/My9IXz)
[hamcrest-core.jar](http://bit.ly/1gbl25b)

and put them into `lib` directory

also download Cobertura files and unpack to `lib/` 

[cobertura](http://sourceforge.net/projects/cobertura/files/latest/download?source=files)

<a name="None"></a>
## ISSUES
cobertura:

cobertura-report doesn't take multiple directories as input (such as `tests` and `src` in `folder`)

jdepend:

    BUILD FAILED
    /var/lib/jenkins/workspace/Java/javaTests/build.xml:263: Problem: failed to create task or type jdepend
    Cause: Could not load a dependent class jdepend/xmlui/JDepend
           It is not enough to have Ant's optional JARs
           you need the JAR files that the optional tasks depend upon.
           Ant's optional task dependencies are listed in the manual.
    Action: Determine what extra JAR files are needed, and place them in one of:
            -/var/lib/jenkins/tools/hudson.tasks.Ant_AntInstallation/Ant/lib
            -/var/lib/jenkins/.ant/lib
            -a directory added on the command line with the -lib argument

    Do not panic, this is a common problem.
    The commonest cause is a missing JAR.

    This is not a bug; it is a configuration problem

This is why i replaced jdepend with `javancss`  

<a name="javacompleteant"></a>
### full `build.xml`[javacompleteant]
```xml
    <project name="name-of-project" default="full-build" basedir=".">

        <!--all properties-->
        <property name="src.dir" value="folder/src"/>
        <property name="tests.dir" value="folder/tests"/>
        <property name="build.dir" value="build"/>
        <property name="classes.dir" value="${build.dir}/classes"/>
        <property name="jar.dir" value="${build.dir}/jar"/>
        <property name="lib.dir" value="lib"/>
        <property name="logs.dir" value="logs"/>
        <property name="docs.dir" value="docs"/>
        <property name="findbugs.dir" value="${lib.dir}/findbugs"/>
        <property name="pmd.dir" value="${lib.dir}/pmd"/>
        <!-- ===================== -->

        <property name="checkstyle.jar.file" value="lib/checkstyle-6.12.1-all.jar"/>
        <property name="javancss.dir" value="lib/javancss-32.53"/>

        <!-- cobertura properties -->
        <property name="cobertura.datafile" value="${build.dir}/cobertura.ser" />
        <property name="cobertura.jar.dir" value="${lib.dir}/cobertura-2.0.3" />
        <property name="cobertura.jar.file" value="${lib.dir}/cobertura-2.0.3/cobertura-2.0.3.jar" />
        <property name="cobertura.dir" value="cobertura"/>
        <property name="cob.instrumented.dir" value="${cobertura.dir}/instrumented" />
        <!-- ===================== -->

        <!--classpath to libraries like junit, cobertura etc.-->
        <path id="classpath.lib">
            <pathelement location="${classes.dir}" />
            <fileset dir="${lib.dir}">
                <include name="**/*.jar"/>
            </fileset>
        </path>
        <!-- ===================== -->

        <!--junit tests including cobertura reports-->
        <path id="cobertura.lib">
            <fileset dir="${cobertura.jar.dir}">
                <include name="cobertura-2.0.3.jar"/>
                <include name="lib/**/*.jar"/>
            </fileset>
        </path>
        <!-- ===================== -->

        <!--run full build consisting of following targets-->
        <target name="full-build"
                depends="prepare, coverage-report, javadoc, checkstyle, jar, findbugs, pmd, javancss"
                description="test build for java applications">
        </target>
        <!-- ===================== -->

        <!--remove files & directories of previous builds-->
        <target name="clean"
                unless="clean.done"
                description="Cleanup build artifacts">
            <echo>=== CLEAN-UP OF BUILD ARTIFACTS FROM PREVIOUS BUILDS ===</echo>
            <delete dir="${build.dir}"/>
            <delete dir="${classes.dir}"/>
            <delete dir="${logs.dir}"/>
            <delete dir="${docs.dir}"/>

            <delete dir="${cobertura.dir}" />
            <delete dir="${cob.instrumented.dir}" />
            <delete dir="${logs.dir}/cobertura" />
            <delete dir="${logs.dir}/cobertura-xml" />
            <delete dir="${logs.dir}/cobertura-summary-xml" />
            <delete dir="${logs.dir}/cobertura-html" />
            <delete file="cobertura.ser"/>

            <property name="clean.done" value="true"/>
        </target>
        <!-- ===================== -->

        <!--create directories for logs, coverage etc.-->
        <target name="prepare"
                unless="prepare.done"
                depends="clean"
                description="Prepare for build">
            <echo>=== PREPARATION OF FOLDER-STRUCTURE ===</echo>
            <mkdir dir="${build.dir}"/>
            <mkdir dir="${classes.dir}"/>
            <mkdir dir="${logs.dir}"/>
            <mkdir dir="${docs.dir}"/>
            <mkdir dir="${jar.dir}"/>

            <mkdir dir="${cobertura.dir}" />
            <mkdir dir="${cob.instrumented.dir}" />
            <mkdir dir="${logs.dir}/cobertura" />

            <property name="prepare.done" value="true"/>
        </target>
        <!-- ===================== -->

        <!--compile sources and tests-->
        <target name="compile"
                depends="prepare">
            <echo>=== COMPILE ===</echo>
            <echo>=== COMPILING ${src.dir} FILES ... ===</echo>
            <javac debug="true" includeantruntime="false" srcdir="${src.dir}" destdir="${classes.dir}">
            </javac>
            <echo>=== COMPILE ===</echo>
            <echo>=== COMPILING ${tests.dir} FILES ... ===</echo>
            <javac includeantruntime="false" srcdir="${tests.dir}" destdir="${classes.dir}">
                <classpath>
                    <pathelement location="${lib.dir}/junit-4.12.jar" />
                    <pathelement location="${lib.dir}/hamcrest-core-1.3.jar" />
                </classpath>
            </javac>
        </target>
        <!-- ===================== -->

        <target name="jar" depends="compile">
            <jar destfile="${jar.dir}/test.jar" basedir="${classes.dir}"/>
        </target>

        <!--setting up cobertura task and instrument-->
        <taskdef classpathref="cobertura.lib"
                 resource="tasks.properties"/>

        <target name="instrument" depends="compile">
            <cobertura-instrument todir="${cob.instrumented.dir}" >
                <fileset dir="${classes.dir}">
                    <include name="**/*.class"/>
                </fileset>
            </cobertura-instrument>
        </target>
        <!-- ===================== -->

        <!--performing cobertura coverage-test after junit test-->
        <target name="coverage-test"
                depends="instrument">

            <junit dir="./" failureproperty="test.failure" printsummary="yes" fork="true">
                <classpath location="${cobertura.jar.file}"/>
                <classpath location="${cob.instrumented.dir}"/>
                <classpath>
                    <path refid="classpath.lib"/>
                </classpath>
                <formatter type="xml" />
                <batchtest fork="yes" todir="${logs.dir}">
                    <fileset dir="${cob.instrumented.dir}">
                        <include name="**/*Test*"/>
                    </fileset>
                </batchtest>

                <!-- cobertura-specific -->
                <sysproperty key="net.sourceforge.cobertura.datafile" file="cobertura.ser" />
                <classpath refid="cobertura.lib" />
            </junit>
        </target>
        <!-- ===================== -->

        <!--make html report of cobertura-->
        <target name="coverage-report"
                depends="coverage-test">
            <echo>=== PERFORMING COVERAGE-REPORT ===</echo>
            <cobertura-report format="html" srcdir="folder" destdir="${logs.dir}/cobertura">
                <fileset dir="${src.dir}">
                    <include name="**/*.java"/>
                </fileset>
                <fileset dir="${tests.dir}">
                    <include name="**/*.java"/>
                </fileset>
            </cobertura-report>
        </target>
        <!-- ===================== -->

        <!--javadoc-->
        <target name="javadoc"
                depends="compile"
                description="generate javadoc">
            <javadoc packagenames="MyUnit"
                     sourcepath="${src.dir}"
                     destdir="${docs.dir}"
                     failonerror="false"
                     use="true"
                     author="true">
                <doctitle><![CDATA[<h1>DiOmega Test</h1>]]></doctitle>
                <bottom><![CDATA[<i>Copyright &#169; 2015 DiOmega. All Rights Reserved.</i>]]></bottom>
                <fileset dir="${src.dir}">
                    <include name="**/*.java"/>
                </fileset>
            </javadoc>
        </target>
        <!-- ===================== -->

        <!--checkstyle-->
        <target name="checkstyle"
                depends="compile">
            <taskdef resource="com/puppycrawl/tools/checkstyle/ant/checkstyle-ant-task.properties"
                     classpath="${lib.dir}/checkstyle-6.12.1-all.jar"/>
            <checkstyle config="${lib.dir}/checkstyle_styles/sun_checks.xml"
                        failOnViolation="false">
                <fileset dir="${src.dir}" includes="**/*.java"/>
                <formatter type="xml" toFile="${logs.dir}/checkstyle_errors.xml"/>
            </checkstyle>
        </target>
        <!-- ===================== -->

        <!--findbugs-->
        <path id="findbugs.classpath">
            <pathelement location="${findbugs.dir}/lib/findbugs-ant.jar"/>
            <pathelement location="${findbugs.dir}/lib/findbugs.jar"/>
        </path>
        <taskdef name="findbugs" classname="edu.umd.cs.findbugs.anttask.FindBugsTask" classpathref="findbugs.classpath" />
        <target name="findbugs"
                depends="jar">
            <findbugs home="${findbugs.dir}"
                      output="xml" outputFile="${logs.dir}/findbugs_report.xml">
                <auxClasspath>
                    <fileset file="${lib.dir}/junit-4.5.jar" />
                </auxClasspath>
                <sourcePath path="${src.dir}"/>
                <fileset dir="${src.dir}">
                    <include name="**/*.java"/>
                </fileset>
                <class location="${jar.dir}"/>
            </findbugs>
        </target>
        <!-- ===================== -->


        <!-- PMD -->
        <path id="pmd.classpath">
            <pathelement location="${build}" />
            <fileset dir="${pmd.dir}/lib/">
                <include name="*.jar" />
            </fileset>
        </path>
        <taskdef name="pmd" classname="net.sourceforge.pmd.ant.PMDTask"
                 classpathref="pmd.classpath" />
        <target name="pmd">

            <pmd>
                <formatter type="xml" toFile="${logs.dir}/pmd_report.xml" />
                <fileset dir="${src.dir}">
                    <include name="**/*.java" />
                </fileset>
                <ruleset>rulesets/java/basic.xml</ruleset>
                <ruleset>rulesets/java/braces.xml</ruleset>
                <ruleset>rulesets/java/comments.xml</ruleset>
                <ruleset>rulesets/java/comments.xml</ruleset>
                <ruleset>rulesets/java/empty.xml</ruleset>
                <ruleset>rulesets/java/controversial.xml</ruleset>
                <ruleset>rulesets/java/coupling.xml</ruleset>
                <ruleset>rulesets/java/design.xml</ruleset>
                <ruleset>rulesets/java/imports.xml</ruleset>
                <ruleset>rulesets/java/logging-java.xml</ruleset>
                <ruleset>rulesets/java/migrating.xml</ruleset>
                <ruleset>rulesets/java/naming.xml</ruleset>
                <ruleset>rulesets/java/optimizations.xml</ruleset>
                <ruleset>rulesets/java/strings.xml</ruleset>
                <ruleset>rulesets/java/sunsecure.xml</ruleset>
                <ruleset>rulesets/java/unusedcode.xml</ruleset>
                <ruleset>rulesets/java/unnecessary.xml</ruleset>
            </pmd>
        </target>
        <!-- ===================== -->

        <!--JavaNCSS-->
        <target name="javancss" depends="compile">
            <taskdef name="javancss"
                     classname="javancss.JavancssAntTask">
                <classpath>
                    <fileset dir="${javancss.dir}/lib" includes="*.jar"/>
                </classpath>
            </taskdef>

            <javancss srcdir="${src.dir}"
                      generateReport="true"
                      outputfile="${logs.dir}/javancss_metrics.xml"
                      format="xml"/>
        </target>
        <!-- ===================== -->

        <!--jdepend-->
     <!--   <target name="jdepend">

            <jdepend format="xml" outputfile="${logs.dir}/jdepend-report.xml">
                <classpath>
                    <pathelement location="${classes.dir}" />
                    <pathelement location="${lib.dir}/jdepend-2.9.1.jar" />
                </classpath>
                <classpath location="${classes.dir}" />
            </jdepend>

            <style basedir="docs" destdir="docs"
                   includes="jdepend-report.xml"
                   style="${lib.dir}/jdepend.xsl" />

        </target>-->
        <!-- ===================== -->

    </project>
```

<a name="javacompletegradle"></a>
### full `build.gradle`[javacompletegradle]
    
```text    
    /*
    *
    -   for gradle only the /lib/checkstyle_styles/ is needed
    -   rest of lib folder is for ant version
    *
    */

    plugins {
      id "net.saliman.cobertura" version "2.2.8"
    }

    println '---PROJECTFOLDER---'
    println project.projectDir

    apply plugin: 'java'
    apply plugin: 'findbugs'
    apply plugin: 'pmd'
    apply plugin: 'jdepend'
    apply plugin: 'checkstyle'

    repositories {
        mavenCentral()
    }

    sourceSets {
        main {
            java {
                srcDirs = ['folder/src']
            }
        }
        test {
            java {
                srcDirs = ['folder/tests']
            }
        }
    }

    dependencies {
        testCompile 'junit:junit:4.12'

        /*
        -   stevesaliman/gradle-cobertura-plugin
        -   ClassNotFoundException on 2.2.7 but 2.2.6
        -   https://github.com/stevesaliman/gradle-cobertura-plugin/issues/75
        -   you need to add the following testRuntime´s
        */
        testRuntime 'org.slf4j:slf4j-nop:1.7.12' // for cobertura
    }

    cobertura {
        coverageFormats = ['html', 'xml']
        coverageReportDir = new File("$buildDir/reports/cobertura")
    }

    findbugs {
        sourceSets = [sourceSets.main]
        ignoreFailures = true
        reportsDir = file("$buildDir/reports/findbugsReports")
        effort = "max"
        reportLevel = "high"
    }

    pmd {
        sourceSets = [sourceSets.main]
        ignoreFailures = true
        reportsDir = file("$buildDir/reports/pmdReports")
        ruleSets = [
            'java-basic',
            'java-braces',
            'java-comments',
            'java-empty',
            'java-controversial',
            'java-coupling',
            'java-design',
            'java-imports',
            'java-logging-java',
            'java-migrating',
            'java-naming',
            'java-optimizations',
            'java-strings',
            'java-sunsecure',
            'java-unusedcode',
            'java-unnecessary'
            ]
    }

    jdepend {
        sourceSets = [sourceSets.main]
        ignoreFailures = true
        reportsDir = file("$buildDir/reports/jdependReports")
    }

    checkstyle {
        sourceSets = [sourceSets.main]
        showViolations = false
        reportsDir = file("$buildDir/reports/checkstyleReports")
        configFile = file("${project.projectDir}/lib/checkstyle_styles/google_checks.xml")
    }

    task javaDocs(type: Javadoc) {
      source = sourceSets.main.allJava
    }
```



