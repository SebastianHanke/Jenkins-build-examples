#Jenkins 
###Installation auf Ubuntu 14.03.2 LTS (Codename: trusty64)

##Installation

###prerequisites

JDK and JRE 
        
        java -version
        java version "1.7.0_79"
        OpenJDK Runtime Environment (IcedTea 2.5.6) (7u79-2.5.6-0ubuntu1.14.04.1)
        OpenJDK 64-Bit Server VM (build 24.79-b02, mixed mode)
        
download from http://openjdk.java.net/install/
        
        sudo apt-get install openjdk-7-jre
        sudo apt-get install openjdk-7-jdk
        
###install Jenkins 
([see website](https://wiki.jenkins-ci.org/display/JENKINS/Installing+Jenkins+on+Ubuntu))  
        
        wget -q -O - https://jenkins-ci.org/debian/jenkins-ci.org.key | sudo apt-key add -
        sudo sh -c 'echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list'
        sudo apt-get update
        sudo apt-get install jenkins
    
###some usefull notes    
Jenkins will be launched as a aemon up on start. See `etc/init.d/jenkins` for more details
The 'jenkins' user is created to run this service
**Log files** will be placed in `/var/log/jenkins/jenkins.log`. Check this if you need troubleshooting Jenkins
`/etc/default/jenkins` will capture configuration parameters for the launch like e.g. `JENKINS_HOME`
    
###start configuration
By default, Jenkins will listen to port **8080** (e.g. http://jenkins-test.local:8080)
Access this port with your browser to **start the configuration**
    
###jenkins browser commands
    
        http://jenkins-installation:8080/restart

        http://jenkins-installation:8080/exit

        http://jenkins-installation:8080/systemInfo
        
###workaround for svn connection problems

        sudo nano /etc/init.d/jenkins
        
look for the following lines
        
            # --user in daemon doesn't prepare environment variables like HOME, USER, LOGNAME or USERNAME,
            # so we let su do so for us now
            $SU -l $JENKINS_USER --shell=/bin/bash -c "$DAEMON $DAEMON_ARGS -- $JAVA $JAVA_ARGS -jar $JENKINS_WAR $JENKINS_ARGS" || return 2
            
the following should be inserted by you: `-Djsse.enableSNIExtension=false` so you get:
            
            $SU -l $JENKINS_USER --shell=/bin/bash -c "$DAEMON $DAEMON_ARGS -- $JAVA $JAVA_ARGS -jar -Djsse.enableSNIExtension=false $JENKINS_WAR $JENKINS_ARGS" || return 2
            
###fixing timezone-differences 

            sudo nano /etc/default/jenkins
            
insert the following there:
            
            JAVA_ARGS="-Dorg.apache.commons.jelly.tags.fmt.timeZone=Europe/Berlin"
            
##JavaScript WebdriverIO-Testrunner with Mocha
            
###Plugins in Jenkins IDE required
    
* Email extension
* NodeJS
* JUnit

###Packages to install globally on jenkins server
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
        
###fixing xunit bug in webdriverio (not fixed by: 2.Oktober 2015)

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
                    
###configure jenkins IDE

Jenkins verwalten -> Konfiguration

  1. NodeJS Installationen
  Choose Installation `NodeJS 4.1.1`
  Global npm packages to install `webdriverio jasmine wdio-jasmine-framework phantomjs`
  
  2. Jenkins Location
  Jenkins URL e.g. http://192.168.56.103:8080/
  Email des Systemadministrators ... fill in ...
  
  3. Extended E-mail Notification
  SMTP server ... fill in ...
  Default Subject `$PROJECT_NAME - Build # $BUILD_NUMBER - $BUILD_STATUS!`
  Default Content `${JELLY_SCRIPT,template="html"}`
  
###create testrunner

Enter name and choose "Free Style" project

Fill in description

###Source-Code-Managment
-> Subversion
    Repository URL ...fill in...
    Credentials ...fill in...
-> Buildumgebung
    Provide Node & npm bin/folder to PATH ...check...
    Installation ...choose the recently created...
-> Buildverfahren
    Shell ausführen
~~`npm install`~~ (dunno need because we installed everything globally before)
~~`selenium-standalone start &`~~ (selenium-server is already running at server-startup)
      `wdio tests/resources/wdio.conf.js`
    
###Post-Build-Aktionen
-> Veröffentliche JUNIT-Testergebnisse
    Testberichte in XML-Format `**/tests/results/*.xml`

-> Editable Email Notification
    Project recipient List  `$DEFAULT_RECIPIENTS`
    Project Reply-To List `$DEFAULT_REPLYTO`
    Content Type `Default Content Type`
    Default Subject `$DEFAULT_SUBJECT`
    Default Content `$DEFAULT_CONTENT`
    
    
##Testing with Mocha(Chai), WebdriverIO and PhantomJS

###Jenkins Konfiguration

NodeJS Konfigurationen

NodeJS Version 4.1.1

Global npm packages to install

    webdriverio mocha chai wdio-mocha-framework phantomjs
    
    if you want to use Jasmine
    
    jasmine wdio-jasmine-framework 
    
Shell command (if selenium is no installed on server)
    
    Selenium-standalone install
    
    sudo npm install selenium-standalone -g
    sudo selenium-standalone install
    
###Configure Build

Folder structure of repository
 
        tests
            resources
                wdio.conf.js
            scripts
                webdriverio
                    diomega.spec.js

so the shell command should look like this:
        
        wdio tests/resources/wdio.conf.js

###the respective config file `wdio.conf.js`

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

###Testing with ES6 generators (will reduce the callback-hell... ;))

The wdio test runner supports ES6 generators. Has been already tested by Sebastian.
The following code therefor works as intended (with `baseUrl: 'http://the-internet.herokuapp.com'` set in wdio.config.js

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
        
###the results will be published in `/tests/results/*.xml`

see `wdio.conf.js`
so post-build-actions will be

publish JUnit results

        **/tests/results/*.xml


####problems with phantomjs

if you try to e.g. .click() an element and the following error ocurrs
 `Problem: Element is not currently visible and may not be manipulated`
this is a webdriverIO bug, that meand that the element you tried to click is currently not visible in phantomjs, because of the default window size
   
in this case try a higher window size before launching your tests
e.g.:

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
        


#PHP

###install phpunit

        

###install Ant if not done by Jenkins

        sudo apt-get install ant
        
###otherwise go to Jenkins and configure Ant-Installation        
        
Jenkins verwalten -> System konfigurieren-> Ant -> Ant Installationen -> Automatisch installieren

Jenkins verwalten -> Plugins verwalten -> Ant plugin installieren
                

            
####editable email notifications

einrichten, um Email-Notifikationen zubekommen

---

#Plugins for php

##PHPunit

###installation on server

        curl -sS https://getcomposer.org/installer | php
        
        sudo mv composer.phar /usr/local/bin/composer
        
        composer global require "phpunit/phpunit=5.0.*"
        
        //sudo nano ~/.bashrc
        
        //export PATH=$PATH:~/.composer/vendor/bin/
        
####upgrade php to 5.6
        
        sudo apt-get -y update
        
        add-apt-repository ppa:ondrej/php5-5.6
        
        sudo apt-get -y update
        
        sudo apt-get -y install php5
        
        php -v //to verify the version
        

####enable php-code-coverage like 'clover'

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

###jenkins plugin to visualize

[XUnit](http://wiki.jenkins-ci.org/display/JENKINS/xUnit+Plugin)

###ant target in `build.xml`
        
        <!--perform phpunit tests with coverage report-->
        
        <target name="phpunit"
                unless="phpunit.done"
                depends="prepare"
                description="Run unit tests with PHPUnit">
            <exec executable="${phpunit}" resultproperty="result.phpunit" taskname="phpunit">
                <arg value="--configuration"/>
                <arg path="${basedir}/build/phpunit.xml"/>
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
                <arg path="${basedir}/build/phpunit.xml"/>
                <arg value="--no-coverage"/>
            </exec>
    
            <property name="phpunit.done" value="true"/>
        </target>

###configuration file (under `/build/phpunit.xml`)

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
                <log type="coverage-clover" target="./logs/clover.xml"/>
                <log type="coverage-crap4j" target="./logs/crap4j.xml"/>
                <log type="junit" target="logs/junit.xml" logIncompleteSkipped="false"/>
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


###jenkins post-build job configuration

Publish JUnit results: `**/build/logs/junit.xml`
Publish Clover results:  **Clover XML Location** `build/logs/clover.xml`



---


##PHP_CodeSniffer

###installation on server

        sudo pear install PHP_CodeSniffer
        
        //command: phpcs
        
###check which coding standards are installed

        phpcs -i
        
        //The installed coding standards are Squiz, PSR2, PSR1, PHPCS, Zend, PEAR and MySource
        
###jenkins plugin to visualize

[Checkstyle](http://wiki.jenkins-ci.org/display/JENKINS/Checkstyle+Plugin)

###ant target in `build.xml`

        <!--find coding standard violations using CodeSniffer (command line version)-->
        
        <target name="phpcs"
                unless="phpcs.done"
                description="Find coding standard violations using PHP_CodeSniffer and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpcs}" taskname="phpcs">
                <arg value="--standard=PSR2" />
                <arg value="--standard=PSR2" />
                <arg value="--extensions=php" />
                <arg value="--ignore=autoload.php" />
                <arg path="${basedir}/build/src" />
                <arg path="${basedir}/build/tests" />
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
                <arg value="--extensions=php" />
                <arg value="--ignore=autoload.php" />
                <arg path="${basedir}/build/src" />
                <arg path="${basedir}/build/tests" />
            </exec>
    
            <property name="phpcs.done" value="true"/>
        </target>
        
###configuration file (under `build/ruleset.xml`)
        
        <?xml version="1.0"?>
        <ruleset name="name-of-your-coding-standard">
            <description>Description of your coding standard</description>
        
            <rule ref="Generic.PHP.DisallowShortOpenTag"/>
            <!-- ... -->
        </ruleset>
        
###jenkins post-build job configuration

Checkstyle: `**/build/logs/checkstyle.xml`

---
        
##PHP_loc - lines of code   
   
###installation on server   
  
        composer global require 'phploc/phploc=*'

        //command: phploc
        
###jenkins plugin to visualize

[Plot](http://wiki.jenkins-ci.org/display/JENKINS/Plot+Plugin)

###ant target in `build.xml`

        <!--count lines of code (command line usage)-->
        
        <target name="phploc"
                unless="phploc.done"
                description="Measure project size using PHPLOC and print human readable output. Intended for usage on the command line.">
            <exec executable="${phploc}" taskname="phploc">
                <arg value="--count-tests" />
                <arg path="${basedir}/build/src" />
                <arg path="${basedir}/build/tests" />
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
                <arg path="${basedir}/build/src" />
                <arg path="${basedir}/build/tests" />
            </exec>
    
            <property name="phploc.done" value="true"/>
        </target>
        
###jenkins post-build job configuration

Plot:
 
**data series file** `build/logs/phploc.csv`

**load data from csv**

---
        
###PHP_Depend

###installation on server   

        composer global require 'pdepend/pdepend=*'
        
        //command: pdepend
        
###jenkins plugin to visualize

[JDepend](http://wiki.jenkins-ci.org/display/JENKINS/JDepend+Plugin)

###ant target in `build.xml`
        

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
                <arg path="${basedir}/build/src" />
            </exec>
    
            <property name="pdepend.done" value="true"/>
        </target>
   
###jenkins post-build job configuration

Report JDepend

**Pre-generated JDepend File**: `build/logs/jdepend.xml`

###integrate Jdependend SVG reports
        
in project description insert 

        <img type="image/svg+xml" height="300" src="ws/build/pdepend/overview-pyramid.svg" width="500"></img>
        <img type="image/svg+xml" height="300" src="ws/build/pdepend/dependencies.svg" width="500"></img>

Jenkins is probably configured to text, therefor 

configure jenkins -> global security -> change markup to html
        
---
        
###PHPMD - PHP Mess Detector

[Clean Code Rules](http://phpmd.org/rules/cleancode.html)
[Code Size Rules](http://phpmd.org/rules/codesize.html)
[Controversial Rules](http://phpmd.org/rules/controversial.html)
[Design Rules](http://phpmd.org/rules/design.html)
[Naming Rules](http://phpmd.org/rules/naming.html)
[Unused Code Rules](http://phpmd.org/rules/unusedcode.html)

###installation on server  

        composer global require 'phpmd/phpmd=*'
                
        //command: phpmd
   
###jenkins plugin to visualize

[PMD](http://wiki.jenkins-ci.org/display/JENKINS/PMD+Plugin)

###ant target in `build.xml`
        
        !--perform mess detection (command line version)-->
        
        <target name="phpmd"
                unless="phpmd.done"
                description="Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpmd}" taskname="phpmd">
                <arg path="${basedir}/build/src" />
                <arg value="text" />
                <arg path="${basedir}/build/phpmd.xml" />
            </exec>
    
            <property name="phpmd.done" value="true"/>
        </target>
    
        <!--perform mess detection (jenkins)-->
    
        <target name="phpmd-ci"
                unless="phpmd.done"
                depends="prepare"
                description="Perform project mess detection using PHPMD and log result in XML format. Intended for usage within a continuous integration environment.">
            <exec executable="${phpmd}" taskname="phpmd">
                <arg path="${basedir}/build/src" />
                <arg value="xml" />
                <arg path="${basedir}/build/phpmd.xml" />
                <arg value="--reportfile" />
                <arg path="${basedir}/build/logs/pmd.xml" />
            </exec>
    
            <property name="phpmd.done" value="true"/>
        </target>
        
###configuration file (under `build/phpmd.xml`)

        <ruleset name="name-of-your-coding-standard"
                 xmlns="http://pmd.sf.net/ruleset/1.0.0"
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0
                              http://pmd.sf.net/ruleset_xml_schema.xsd"
                 xsi:noNamespaceSchemaLocation="http://pmd.sf.net/ruleset_xml_schema.xsd">
            <description>Description of your coding standard</description>
        
            <rule ref="rulesets/codesize.xml/CyclomaticComplexity" />
            <!-- ... -->
        </ruleset>
        
###jenkins post-build job configuration

Report PMD anylsis

**filename PMD results**: `build/logs/pmd.xml`
        
---
        
###PHPCPD - Copy-Paste-Detector

###installation on server  
        
        composer global require "sebastian/phpcpd=*"
        
        //command: phpcpd
        
###jenkins plugin to visualize

[Duplicate Code Scanner Plug-in](http://wiki.jenkins-ci.org/x/X4IuAg)

###ant target in `build.xml`
        
        <!--find duplicate code (command line version)-->
        
        <target name="phpcpd"
                unless="phpcpd.done"
                description="Find duplicate code using PHPCPD and print human readable output. Intended for usage on the command line before committing.">
            <exec executable="${phpcpd}" taskname="phpcpd">
                <arg path="${basedir}/build/src" />
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
                <arg path="${basedir}/build/src" />
            </exec>
    
            <property name="phpcpd.done" value="true"/>
        </target>
 
###jenkins post-build job configuration

Report Duplicate Code Scanner results

**filename**: `**/build/logs/pmd-cpd.xml`
        
---
        
###PHP_dox

###installation on server  
        
        wget https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-0.8.0.phar
        
        chmod +x phpdox-0.8.0.phar
        
        sudo mv phpdox-0.8.0.phar /usr/bin/phpdox
        
        //if not installed:
        sudo apt-get install php5-xsl
        
###jenkins plugin to visualize

[HTML publisher](http://wiki.jenkins-ci.org/display/JENKINS/HTML+Publisher+Plugin)

###ant target in `build.xml`
        
        <target name="phpdox"
                unless="phpdox.done"
                depends="phploc-ci,phpcs-ci,phpmd-ci"
                description="Generate project documentation using phpDox">
            <exec executable="${phpdox}" dir="${basedir}/build" taskname="phpdox"/>
    
            <property name="phpdox.done" value="true"/>
        </target>
        
###configuration file (under `build/phpdox.xml`)

        <?xml version="1.0"?>
        <phpdox xmlns='http://xml.phpdox.net/config'>
            <project name="name-of-project" source="${basedir}/src" workdir="${basedir}/phpdox">
                <collector publiconly="false">
                    <include mask="*.php" />
                </collector>
        
                <generator output="${basedir}/api">
                    <enrich base="${basedir}/logs">
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
        
###jenkins post-build job configuration

Publish HTML reports

**directory**: `build/api`
**index page**: `index.html`
**report title**: `HTML Report`
        
---
        
###PHP_CodeBrowser (not in use!)
        
        composer global require "mayflower/php-codebrowser=~1.1"
        
        //command: phpcb
        
---

###PHPMetrics

###installation on server  

        composer global require "halleck45/phpmetrics"
        
        //command: phpmetrics
        
###jenkins plugin to visualize

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

###ant target in `build.xml`
        
        <target name="phpmetrics"
                unless="phpmetrics.done"
                description="generate phpmetrics reports">
            <exec executable="${phpmetrics}" taskname="phpmetrics">
                <arg value="--report-xml=${basedir}/build/logs/phpmetrics.xml"/>
                <arg value="--violations-xml=${basedir}/build/logs/violations.xml"/>
                <arg value="--report-html=${basedir}/build/logs/quality.html"/>
                <arg path="${basedir}/build/src"/>
            </exec>
    
            <property name="phpmetrics.done" value="true"/>
        </target>
        
###jenkins post-build job configuration
        
######for phpmetrics.xml option:
   Plot

   **data series file** `build/logs/phpmetrics.xml`

   **load data from xml using xpath**
   **xpath result type** number

######for violations.xml option:
   Violation

   under pmd `build/logs/violations.xml`

######for phpmetrics.html option:
   sidebar-links(right under description) 
   **link url** `http://192.168.56.103:8080/job/PHPUnit/ws/build/logs/quality.html`
   **link text** `PhpMetrics report`
   **link icon** `document.gif`
        
to understand the results have a look at: ([how-to-read-report](http://www.phpmetrics.org/documentation/how-to-read-report.html))


##complete working solution `build.xml`

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
            <!--<property name="phpcb" value="/home/vagrant/.composer/vendor/bin/phpcb"/>-->
        
        
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
                    <arg path="${basedir}/build/src" />
                    <arg path="${basedir}/build/tests" />
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
                    <arg path="${basedir}/build/src" />
                    <arg path="${basedir}/build/tests" />
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
                    <arg path="${basedir}/build/src" />
                </exec>
        
                <property name="pdepend.done" value="true"/>
            </target>
        
            <!--perform mess detection (command line version)-->
        
            <target name="phpmd"
                    unless="phpmd.done"
                    description="Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing.">
                <exec executable="${phpmd}" taskname="phpmd">
                    <arg path="${basedir}/build/src" />
                    <arg value="text" />
                    <arg path="${basedir}/build/phpmd.xml" />
                </exec>
        
                <property name="phpmd.done" value="true"/>
            </target>
        
            <!--perform mess detection (jenkins)-->
        
            <target name="phpmd-ci"
                    unless="phpmd.done"
                    depends="prepare"
                    description="Perform project mess detection using PHPMD and log result in XML format. Intended for usage within a continuous integration environment.">
                <exec executable="${phpmd}" taskname="phpmd">
                    <arg path="${basedir}/build/src" />
                    <arg value="xml" />
                    <arg path="${basedir}/build/phpmd.xml" />
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
                    <arg value="--standard=PSR2" />
                    <arg value="--extensions=php" />
                    <arg value="--ignore=autoload.php" />
                    <arg path="${basedir}/build/src" />
                    <arg path="${basedir}/build/tests" />
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
                    <arg value="--extensions=php" />
                    <arg value="--ignore=autoload.php" />
                    <arg path="${basedir}/build/src" />
                    <arg path="${basedir}/build/tests" />
                </exec>
        
                <property name="phpcs.done" value="true"/>
            </target>
        
            <!--find duplicate code (command line version)-->
        
            <target name="phpcpd"
                    unless="phpcpd.done"
                    description="Find duplicate code using PHPCPD and print human readable output. Intended for usage on the command line before committing.">
                <exec executable="${phpcpd}" taskname="phpcpd">
                    <arg path="${basedir}/build/src" />
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
                    <arg path="${basedir}/build/src" />
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
                    <arg path="${basedir}/build/phpunit.xml"/>
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
                    <arg path="${basedir}/build/phpunit.xml"/>
                    <arg value="--no-coverage"/>
                </exec>
        
                <property name="phpunit.done" value="true"/>
            </target>
        
            <!-- phpcb should be called after xml file generation -->
           <!-- <target name="phpcb">
                <exec executable="${phpcb}">
                    <arg line="&#45;&#45;log '${basedir}/build/logs/'
                           &#45;&#45;output '${basedir}/build/'
                           &#45;&#45;source '${basedir}/build/src/'" />
                </exec>
            </target>-->
            <!--generate documentation-->
        
            <target name="phpmetrics"
                    unless="phpmetrics.done"
                    description="generate phpmetrics reports">
                <exec executable="${phpmetrics}" taskname="phpmetrics">
                    <arg value="--report-xml=${basedir}/build/logs/phpmetrics.xml"/>
                    <arg value="--violations-xml=${basedir}/build/logs/violations.xml"/>
                    <arg value="--report-html=${basedir}/build/logs/quality.html"/>
                    <arg path="${basedir}/build/src"/>
                </exec>
        
                <property name="phpmetrics.done" value="true"/>
            </target>
        
            <target name="phpdox"
                    unless="phpdox.done"
                    depends="phploc-ci,phpcs-ci,phpmd-ci"
                    description="Generate project documentation using phpDox">
                <exec executable="${phpdox}" dir="${basedir}/build" taskname="phpdox"/>
        
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
        
        
#Java

##download following .jar

[junit.jar](http://bit.ly/My9IXz)
[hamcrest-core.jar](http://bit.ly/1gbl25b)

and put them into `lib` directory

also download Cobertura files and unpack to `lib/` 

[cobertura](http://sourceforge.net/projects/cobertura/files/latest/download?source=files)

ISSUE!!!!
cobertura-report doesn't take multiple directories as input (such as `tests` and `src` in `folder`)

jdepend
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
